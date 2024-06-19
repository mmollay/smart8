<?php
//http://deineDomain.de/.../fetch_orders.php?token=52a36a36e2e6da849685b71f466dde56
//http://localhost/smart7/ssi_trader/exec/fetch_orders.php?token=52a36a36e2e6da849685b71f466dde56

// für cronjob  
//*/5 * * * * php /var/www/ssi/smart7/ssi_trader/exec/fetch_orders.php 52a36a36e2e6da849685b71f466dde56
//Shell
//php /Applications/XAMPP/htdocs/smart/smart7/ssi_trader/exec/fetch_orders.php 52a36a36e2e6da849685b71f466dde56

//$apiUrl = 'orders.txt';

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SECRET_TOKEN', '52a36a36e2e6da849685b71f466dde56');

require_once (__DIR__ . "/../t_config.php");



$token = php_sapi_name() === 'cli' ? ($argc > 1 ? $argv[1] : null) : ($_GET['token'] ?? null);
$fetchMethod = php_sapi_name() === 'cli' ? 'shell' : 'http';
$sessionDataFile = __DIR__ . '/session_data.txt';

$tokensNeedUpdate = false;

if ($fetchMethod == 'shell') {
    if (file_exists($sessionDataFile)) {
        // Überprüfen, wann die Datei zuletzt geändert wurde
        $lastModified = filemtime($sessionDataFile);
        $currentTime = time();
        $hoursSinceLastUpdate = ($currentTime - $lastModified) / 3600;

        if ($hoursSinceLastUpdate >= 12) {
            // Die Token müssen erneuert werden
            $tokensNeedUpdate = true;
        } else {
            // Session-Daten aus der Datei lesen
            $sessionData = json_decode(file_get_contents($sessionDataFile), true);
            $_SESSION['token'] = $sessionData['token'] ?? [];
        }
    } else {
        $tokensNeedUpdate = true;
    }

    if ($tokensNeedUpdate) {
        // Logik zum Erneuern der Token hier
        // Nachdem die Token erfolgreich erneuert wurden, schreibe sie in session_data.txt
        $sessionData = ['token' => $_SESSION['token']];
        file_put_contents($sessionDataFile, json_encode($sessionData));
    }
}

if (php_sapi_name() === 'cli') {
    // Pfad zur Datei definieren
    $sessionDataFile = __DIR__ . '/session_data.txt';

    // Überprüfen, ob die Datei existiert
    if (file_exists($sessionDataFile)) {
        // Session-Daten aus der Datei lesen
        $sessionData = json_decode(file_get_contents($sessionDataFile), true);

        // Session-Daten wiederherstellen
        $_SESSION['token'] = $sessionData['token'];
    } else {
        echo "Session-Daten nicht gefunden.\n";
        exit;
    }
}

// Verwende mysqli_connect anstelle von new mysqli
//$mysqli = mysqli_connect('127.0.0.1', $cfg_mysql['user'], $cfg_mysql['password'], $cfg_mysql['db']);


if (!$mysqli) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}


function jsonResponse($response)
{
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function validateToken($token)
{
    return $token === SECRET_TOKEN;
}

function handleDatabaseError($mysqli, &$response)
{
    if (mysqli_connect_errno()) {
        $response['success'] = false;
        $response['errors'][] = ['message' => "Connection failed: " . mysqli_connect_error(), 'type' => 'error'];
        jsonResponse($response);
    }
}

$response = ['success' => true, 'data' => [], 'errors' => []];

if (!validateToken($token)) {
    $response['success'] = false;
    $response['errors'][] = ['message' => "Unauthorized access! Incorrect or missing token.", 'type' => 'error'];
    jsonResponse($response);
}

handleDatabaseError($mysqli, $response);

$serverQuery = "SELECT url, server_id, a.broker_id, user as account
    FROM ssi_trader.servers a 
        LEFT JOIN ssi_trader.broker b ON a.broker_id = b.broker_id 
            WHERE active = 1";

if ($result = mysqli_query($mysqli, $serverQuery)) {
    while ($row = mysqli_fetch_assoc($result)) {
        processServerRow($row, $mysqli, $response);
    }
    //Zuweisung der Strategien
    assignStrategiesFromAssignments($mysqli);

} else {
    $response['success'] = false;
    $response['errors'][] = ['message' => "No server URLs found.", 'type' => 'error'];
    jsonResponse($response);
}

jsonResponse($response);


mysqli_close($mysqli);

function processServerRow($row, $mysqli, &$response)
{
    $apiUrl = $row['url'] . "/history";
    $token = $_SESSION['token'][$row['server_id']] ?? null;

    if (!$token) {
        $response['errors'][] = ['message' => "Fehler beim Abrufen des Tokens.", 'type' => 'error'];
        return;
    }

    if (empty($row['server_id']) || empty($row['broker_id'])) {
        $response['errors'][] = ['message' => "Fehler: server_id oder broker_id fehlt für die URL $apiUrl.", 'type' => 'error'];
        return;
    }

    $jsonString = sendCurlRequest($apiUrl, '', $token);
    $array_orders = json_decode($jsonString, true);

    processOrders($array_orders, $row, $mysqli, $response);
}

function processOrders($array_orders, $row, $mysqli, &$response)
{
    if (is_array($array_orders) && isset($array_orders['history'])) {
        $orders = $array_orders['history'];
        insertOrders($orders, $row, $mysqli, $response);
    } else if (isset($array_orders['error'])) {
        $response['errors'][] = ['message' => "Error from Trader-Server: " . $array_orders['error'], 'type' => 'error'];
    } else {
        $response['data'][] = ['message' => "No data received from API.", 'type' => 'info'];
    }
}

function insertOrders($orders, $row, $mysqli, &$response)
{
    $insertedCount = 0;
    $alreadyInsertedCount = 0;
    $lotgroup_id = 0;
    $oldtime = '';

    foreach ($orders as $order) {
        $symbolQuery = "SELECT symbol_id FROM ssi_trader.symbols WHERE symbol = ?";
        if ($stmt = mysqli_prepare($mysqli, $symbolQuery)) {
            mysqli_stmt_bind_param($stmt, "s", $order[15]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $symbol_id = mysqli_fetch_assoc($result)['symbol_id'];
            } else {
                $insertSymbol = "INSERT INTO ssi_trader.symbols (symbol) VALUES (?)";
                if ($stmtInsert = mysqli_prepare($mysqli, $insertSymbol)) {
                    mysqli_stmt_bind_param($stmtInsert, "s", $order[15]);
                    mysqli_stmt_execute($stmtInsert);
                    $symbol_id = mysqli_insert_id($mysqli);
                }
            }
        }

        if ($order[2] != $oldtime && $order[5] == 1) {
            $lotgroup_id = $order[1];
        }

        $strategy = 0;
        $trash = 0;
        $stmt = mysqli_prepare($mysqli, "INSERT INTO ssi_trader.orders (ticket, order_id, time, time_msc, type, entry, magic, position_id, reason, volume, price, commission, swap, profit, fee, symbol_id, comment, external_id, server_id, broker_id, lotgroup_id, trash, account, strategy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ticket = VALUES(ticket)");
        mysqli_stmt_bind_param($stmt, "iiiiiiiiiddddddissiiisis", $order[0], $order[1], $order[2], $order[3], $order[4], $order[5], $order[6], $order[7], $order[8], $order[9], $order[10], $order[11], $order[12], $order[13], $order[14], $symbol_id, $order[16], $order[17], $row['server_id'], $row['broker_id'], $lotgroup_id, $trash, $row['account'], $strategy);
        $oldtime = $order[2];
        mysqli_stmt_execute($stmt);

        $affectedRows = mysqli_stmt_affected_rows($stmt);

        if ($affectedRows == 1) {
            $insertedCount++;
        } elseif ($affectedRows == 0) {
            $alreadyInsertedCount++;
        }
    }

    // Nach dem Einfügen/Aktualisieren der Bestellungen, protokollieren Sie den Vorgang.
    global $fetchMethod;
    if ($insertedCount > 0 || $alreadyInsertedCount > 0) {
        $logStmt = mysqli_prepare($mysqli, "INSERT INTO fetch_logs (server_id, broker_id, fetched_count, already_inserted_count, fetch_method) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($logStmt, "iiiis", $row['server_id'], $row['broker_id'], $insertedCount, $alreadyInsertedCount, $fetchMethod);
        mysqli_stmt_execute($logStmt);
    }

    if ($insertedCount > 0) {
        $response['data'][] = ['message' => "$insertedCount new orders successfully inserted.", 'type' => 'success'];
    }
    if ($alreadyInsertedCount > 0) {
        $response['data'][] = ['message' => "$alreadyInsertedCount orders were already inserted.", 'type' => 'info'];
    }
}

function assignStrategiesFromAssignments($mysqli)
{
    // Holen wir uns die Strategie-Zuweisung-Datensätze
    $strategyAssignmentsStmt = $mysqli->prepare("SELECT * FROM strategy_assignments ORDER BY `timestamp` ASC");
    $strategyAssignmentsStmt->execute();
    $strategyAssignments = $strategyAssignmentsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $strategyAssignmentsStmt->close();

    // Bereiten wir die SQL-Abfrage vor
    $updateOrderStmt = $mysqli->prepare("UPDATE orders SET strategy = ? WHERE server_id = ? AND `time` > ? ORDER BY `time` ASC");

    // Zählen wir die Anzahl der Zuweisungen pro Strategie und Server
    $strategyCount = array();
    $serverCount = array();

    // Iterieren wir über die Strategie-Zuweisungen
    foreach ($strategyAssignments as $assignment) {
        $strategy = $assignment['strategy'];
        $serverId = $assignment['server_id'];
        $timestamp = strtotime($assignment['timestamp']);  // Konvertieren wir den Zeitstempel in Unix-Zeit

        // Aktualisieren wir die Orders mit der entsprechenden Strategie
        $updateOrderStmt->bind_param("sis", $strategy, $serverId, $timestamp);
        $updateOrderStmt->execute();
        $affectedRows = $updateOrderStmt->affected_rows;

        // Zählen wir die Anzahl der Zuweisungen pro Strategie
        if (!isset($strategyCount[$strategy])) {
            $strategyCount[$strategy] = $affectedRows;
        } else {
            $strategyCount[$strategy] += $affectedRows;
        }

        // Zählen wir die Anzahl der Zuweisungen pro Server
        if (!isset($serverCount[$serverId])) {
            $serverCount[$serverId] = $affectedRows;
        } else {
            $serverCount[$serverId] += $affectedRows;
        }
    }

    // Schließen wir die vorbereiteten Abfragen
    $updateOrderStmt->close();

    // // Geben wir die Anzahl der Zuweisungen pro Strategie aus
    // foreach ($strategyCount as $strategy => $count) {
    //     echo "Strategie '$strategy' wurde $count mal zugewiesen.<br> ";
    // }
    // echo "<hr>";
    // // Geben wir die Anzahl der Zuweisungen pro Server aus
    // foreach ($serverCount as $serverId => $count) {
    //     echo "Server ID '$serverId' hat $count Zuweisungen erhalten.<br> ";
    // }
}