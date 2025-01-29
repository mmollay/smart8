<?php


function getPositionsSummary($serverIp, $token)
{
    // Send the cURL request and get the result
    $json_string = sendCurlRequest($serverIp . "/openPositions", '', $token);

    // Convert the JSON string into an array
    $array = json_decode($json_string, true);

    $totalLots = 0;

    // Check if the 'position' key exists and is formatted correctly
    if (isset($array['position']) && is_array($array['position'])) {

        foreach ($array['position'] as $position) {
            // Check if the position is Buy (1) or Sell (0) at index 5
            $isBuy = $position[5] == 1; // Buy if 1, Sell if 0
            $lotSize = $position[9]; // Lot size at index 9
            $daxValue = $position[10]; // DAX value at index 10
            $daxValue = $position[13]; // DAX value at index 10

            // If Buy, add the lot size, if Sell, subtract the lot size
            if ($isBuy) {
                $totalLots += $lotSize;
            } else {
                $totalLots -= $lotSize;
            }
        }

        $margin = abs($totalLots * $daxValue / 100) * 1.09; //Margin zuschlag 9%
        $totalLots = abs($totalLots);

        // Number of positions
        $numberOfPositions = count($array['position']);

        // Sum of values at the 9th position
        //$sumOfValuesAtPosition9 = array_sum(array_column($array['position'], 9)); //Lots

        $sumOfValuesAtPosition15 = array_sum(array_column($array['position'], 15)); //Price aktuell


        return [
            'numberOfPositions' => $numberOfPositions,
            'totalLots' => $totalLots,
            'sumOfValuesAtPositionPrice15' => $sumOfValuesAtPosition15,
            'margin' => $margin
        ];
    } else {
        // Return an error message if the expected data were not found
        return [
            'error' => 'Positions could not be found or processed.',
        ];
    }
}


// Funktion zum Abrufen der Strategienamen
// ema_form.php und f_server.php
function getStrategyNames($url, $token)
{
    // sendCurlRequest-Funktion muss bereits definiert sein oder Sie müssen hier eine cURL-Anfrage einbauen
    $json_string = sendCurlRequest($url . "/getStrategies", '', $token);

    // Konvertieren des JSON-Strings in ein PHP-Array
    $data = json_decode($json_string, true);

    $arrayStrategies = [];

    // Überprüfen, ob die 'strategies'-Daten vorhanden und korrekt sind
    if (isset($data['strategies']) && is_array($data['strategies'])) {
        // Durchlaufen des 'strategies' Arrays und Erstellen eines neuen assoziativen Arrays,
        // in dem der 'strategyName' sowohl der Schlüssel als auch der Wert ist
        foreach ($data['strategies'] as $strategy) {
            $name = $strategy['strategyName'];
            $arrayStrategies[$name] = $name;
        }
    }

    // Rückgabe des assoziativen Arrays, in dem 'strategyName' Schlüssel und Wert ist
    return $arrayStrategies;
}

function getToken($server_id, $serverIp, $username, $password, $mysqli)
{
    $loginUrl = $serverIp . "/login";
    $loginData = array('username' => $username, 'password' => $password);

    // Angenommen, sendCurlRequest ist eine Funktion, die eine cURL-Anfrage sendet und die Antwort als String zurückgibt.
    $jsonStringLogin = sendCurlRequest($loginUrl, $loginData, 'POST');
    $obj = json_decode($jsonStringLogin);

    if ($obj !== null && property_exists($obj, 'token')) {
        // Der Token wurde erfolgreich erhalten, jetzt in der Datenbank speichern,
        // und aktualisiere das Feld 'timestamp' mit dem aktuellen Zeitstempel
        $sql = "UPDATE ssi_trader.servers SET token = ?, timestamp = CURRENT_TIMESTAMP WHERE server_id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $obj->token, $server_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return $obj->token; // Token erfolgreich aktualisiert und gespeichert
            } else {
                $stmt->close();
                // Behandle den Fall, dass der Token nicht gespeichert werden konnte
                throw new Exception("Token konnte in der Datenbank nicht aktualisiert werden.");
            }
        } else {
            // Fehler beim Vorbereiten des Statements
            throw new Exception("Fehler beim Aktualisieren des Tokens in der Datenbank.");
        }
    } else {
        // Token konnte nicht abgerufen werden, handle diesen Fall entsprechend
        return null; // oder throw new Exception("Token konnte nicht abgerufen werden.");
    }
}


function getMT5BrokerData($mysqli, $server_id)
{
    // Überprüft, ob die Funktion mit einem gültigen server_id aufgerufen wurde
    if (empty($server_id)) {
        return "Invalid server ID specified.";
    }

    // SQL-Anfrage, um Broker-Daten in Verbindung mit der Server-ID auszulesen
    $sql = "SELECT servers.*, broker.user, broker.password, broker.broker_server 
            FROM ssi_trader.servers 
            LEFT JOIN ssi_trader.broker ON servers.broker_id = broker.broker_id 
            WHERE servers.server_id = ?";

    // Prepared Statement, um SQL-Injection zu vermeiden
    if ($stmt = $mysqli->prepare($sql)) {
        // Bindet die Parameter an die SQL-Anfrage
        $stmt->bind_param("i", $server_id);

        // Führt die Anfrage aus
        $stmt->execute();

        // Holt das Ergebnis der Anfrage
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Extrahiert die benötigten Daten aus der Antwort
            $data = [
                'account' => $row['user'],
                'password' => $row['password'],
                'server' => $row['broker_server']
            ];

            // Gibt die gesammelten Daten zurück
            return $data;
        } else {
            // Keine Daten gefunden
            return "No data found for server_id: " . $server_id;
        }
    } else {
        // Fehler beim Vorbereiten der SQL-Anfrage
        return "Error preparing SQL query.";
    }
}


//controlMT5($mysqli, $server_id, $token, 'start');
//controlMT5($mysqli, $server_id, $token, 'stop');
function controlMT5($mysqli, $server_id, $token, $action)
{
    if (!in_array($action, ['start', 'stop'])) {
        return "Invalid action specified.";
    }

    // SQL-Anfrage, um Broker-Daten in Verbindung mit der Server-ID auszulesen
    $sql = "SELECT servers.*, broker.user, broker.password, broker.broker_server FROM ssi_trader.servers LEFT JOIN ssi_trader.broker ON servers.broker_id = broker.broker_id WHERE servers.server_id = ?";

    // Prepared Statement, um SQL-Injection zu vermeiden
    if ($stmt = $mysqli->prepare($sql)) {
        // Bindet die Parameter an die SQL-Anfrage
        $stmt->bind_param("i", $server_id);

        // Führt die Anfrage aus
        $stmt->execute();

        // Holt das Ergebnis der Anfrage
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Extrahiert die benötigten Daten aus der Antwort
            $user = $row['user'];
            $password = $row['password'];
            $broker_server = $row['broker_server'];
            $serverIp = $row['url'];

            // URL für die Anfrage, abhängig von der Aktion
            $url = $serverIp . "/" . $action . "MT5";

            // Daten für die Anfrage
            $data = array('account' => $user, 'password' => $password, 'server' => $broker_server);

            // Sendet die Anfrage
            $jsonString = sendCurlRequest($url, $data, $token);

            // Gibt die Antwort zurück
            return $jsonString;
        } else {
            // Keine Daten gefunden
            return "No data found for server_id: " . $server_id;
        }
    } else {
        // Fehler beim Vorbereiten der SQL-Anfrage
        return "Error preparing SQL query.";
    }
}

function getAllServerIps($mysqli)
{
    $ips = [];
    $todayStart = strtotime("today midnight");
    $todayEnd = strtotime("tomorrow midnight") - 1;
    $yesterdayStart = strtotime("yesterday midnight");
    $yesterdayEnd = strtotime("today midnight") - 1;

    // Erweiterte Abfrage mit JOIN auf die Broker-Tabelle und Berechnung des täglichen Profits und des Profits vom Vortag
    $query = "SELECT s.server_id, s.url, s.name, b.broker_id, b.real_account, strategy_default, contract_default, lotsize, title broker_matchcode, b.user, 
                     (SELECT SUM(profit) FROM ssi_trader.orders WHERE server_id = s.server_id AND time BETWEEN ? AND ? AND trash = 0) AS daily_profit,
                     (SELECT SUM(profit) FROM ssi_trader.orders WHERE server_id = s.server_id AND time BETWEEN ? AND ? AND trash = 0) AS previous_day_profit
              FROM ssi_trader.servers AS s
              JOIN ssi_trader.broker AS b ON s.broker_id = b.broker_id 
              WHERE s.active = 1";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param('iiii', $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {

            //openPositions abfragen
            //$arrayPositionSummnery = getPositionsSummary($row['url'], $_SESSION['token'][$row['server_id']]);
            $ips[] = [
                'url' => $row['url'],
                'name' => $row['name'],
                'server_id' => $row['server_id'],
                'broker_id' => $row['broker_id'],
                'broker_matchcode' => $row['broker_matchcode'],
                'real_account' => $row['real_account'],
                'strategy_default' => $row['strategy_default'],
                'contract_default' => $row['contract_default'],
                'account' => $row['user'], // 'account' ist das gleiche wie 'real_account
                'lotsize' => $row['lotsize'],
                'daily_profit' => round($row['daily_profit'] ?? 0, 2), // Täglichen Profit hinzufügen und runden
                'previous_day_profit' => round($row['previous_day_profit'] ?? 0, 2) // Profit vom Vortag hinzufügen und runden

            ];
        }
        $stmt->close();
    }
    return $ips;
}




//erzeugen für den Client form_edit2.php
function generateSecureToken($length = 64)
{
    // Stellen Sie sicher, dass die Länge nicht zu klein ist, um die Sicherheit zu gewährleisten.
    if ($length < 16) {
        throw new Exception('Token length cannot be less than 16 characters');
    }

    try {
        // Generiert eine sichere zufällige Bytefolge und konvertiert sie in einen Hexadezimalwert
        $token = bin2hex(random_bytes($length));
        return $token;
    } catch (Exception $e) {
        // Fehlerbehandlung, falls die Generierung fehlschlägt
        // In einem Produktionscode sollten Sie hier angemessene Fehlerbehandlungen durchführen
        die('Fehler bei der Generierung des Tokens: ' . $e->getMessage());
    }
}



// Funktion für cURL-Requests
function sendCurlRequest($url, $data = '', $token = '')
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = ['Content-Type: application/json'];
    if (!empty($token)) {
        $headers[] = 'Authorization: ' . $token; // Token zum Header hinzufügen
    }

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        $response = "connection_failed: $url, Error: $error_msg";
    }

    curl_close($ch);
    return $response;
}


function getServerIdGetUrl($mysqli, $server_id)
{

    // Überprüfen, ob die übergebene server_id gültig ist
    if (!is_numeric($server_id) || $server_id <= 0) {
        return ["success" => false, "message" => "Ungültige 'server_id'"];
    }

    // Das SQL-Statement vorbereiten
    $stmt = $mysqli->prepare("
        SELECT url 
        FROM ssi_trader.servers 
        WHERE server_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        // Fehler beim Vorbereiten des Statements
        return ["success" => false, "message" => "Fehler beim Vorbereiten des Statements: " . $mysqli->error];
    }

    // Parameter binden und die Abfrage ausführen
    $stmt->bind_param("i", $server_id);
    $stmt->execute();

    // Ergebnis abrufen
    $result = $stmt->get_result();


    if ($row = $result->fetch_assoc()) {

        return ["success" => true, "url" => $row["url"]];
    } else {
        // Kein Ergebnis gefunden
        return ["success" => false, "message" => "Keine IP-Adresse gefunden."];
    }

    // Ressourcen freigeben
    $stmt->close();
}


function getSingleServerIpByUserId($mysqli, $user_id)
{

    // Überprüfen, ob die übergebene user_id gültig ist
    if (!is_numeric($user_id) || $user_id <= 0) {
        return "Ungültige 'user_id'";
    }

    // Das SQL-Statement vorbereiten
    $stmt = $mysqli->prepare("
        SELECT s.url 
        FROM ssi_trader.setting st 
        JOIN ssi_trader.broker b ON st.broker_id = b.broker_id 
        JOIN ssi_trader.servers s ON b.server_id = s.server_id 
        WHERE st.user_id = ?
        LIMIT 1
    ");


    if (!$stmt) {
        // Fehler beim Vorbereiten des Statements
        return "Fehler beim Vorbereiten des Statements: " . $mysqli->error;
    }

    // Parameter binden und die Abfrage ausführen
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Ergebnis abrufen
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $url = $row["url"];
    } else {
        // Kein Ergebnis gefunden
        $url = "Keine IP-Adresse gefunden.";
    }

    // Ressourcen freigeben
    $stmt->close();

    // Die gefundene IP-Adresse oder Fehlermeldung zurückgeben
    return $url;
}


function fetchStocksData($cfg_mysql)
{
    // Verbindung zur Datenbank herstellen
    $connection = new mysqli($cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'], $cfg_mysql['db']);
    if ($connection->connect_error) {
        return "Verbindung fehlgeschlagen: " . $connection->connect_error;
    }

    // SQL-Query vorbereiten
    $query = "SELECT * FROM ssi_trader.stocks_data ORDER BY time DESC LIMIT 200";
    $result = $connection->query($query);

    if ($result) {
        $smart_list = "<table class='ui very compact basic celled table'>";
        $smart_list .= "<thead><tr><th>Buy</th><th>Sell</th><th>Time</th><th>Price</th></tr></thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            $smart_list .= "<tr><td" . ($row['buy'] > $row['sell'] ? " class='positive'" : "") . ">" . htmlspecialchars($row['buy']) . "</td><td" . ($row['sell'] > $row['buy'] ? " class='positive'" : "") . ">" . htmlspecialchars($row['sell']) . "</td><td>" . htmlspecialchars($row['time']) . "</td><td>" . htmlspecialchars($row['price']) . "</td></tr>";
        }

        $smart_list .= "</tbody></table>";
    } else {
        $smart_list = "Fehler beim Abrufen der Daten aus der Datenbank.";
    }

    $connection->close();
    return $smart_list;
}


function fetchAllServers($mysqli)
{
    $serversArray = [];
    $query = "SELECT url, server_id, CONCAT(url, ' (', name, ')') AS UrlName FROM ssi_trader.servers WHERE active = 1"; // Nur aktive Server werden abgerufen
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $serversArray[] = [
                'server_id' => $row['server_id'],
                'UrlName' => $row['UrlName'],
                'url' => $row['url'],
                'token' => $row['token']
            ];
        }
        $result->free();
    }
    return $serversArray;
}


/**
 * Holt die Bezeichnung einer Strategie anhand der group_id.
 *
 * @param mysqli $mysqli Eine mysqli-Instanz, die mit der Datenbank verbunden ist.
 * @param int $groupId Die ID der Gruppe (Strategie), die ausgelesen werden soll.
 * @return array|null Gibt ein assoziatives Array mit 'title' und 'text' der Strategie zurück, oder null, falls keine Strategie gefunden wurde.
 */
function fetchStrategy($mysqli, $groupId)
{
    $stmt = $mysqli->prepare("SELECT title, text FROM ssi_trader.hedging_group WHERE group_id = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return ['title' => $row['title'], 'text' => $row['text']];
    }
    return null; // Keine Strategie mit der gegebenen ID gefunden
}

function getServerUrl($mysqli, $serverId)
{
    // Sicherstellen, dass die server_id numerisch ist, um SQL-Injection zu vermeiden
    if (!is_numeric($serverId)) {
        return null;
    }

    // Vorbereitete Anweisung, um die URL anhand der server_id zu holen
    $stmt = $mysqli->prepare("SELECT url FROM ssi_trader.servers WHERE server_id = ?");
    $stmt->bind_param("i", $serverId);
    $stmt->execute();

    // Ergebnis holen
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['url']; // Die URL zurückgeben, wenn ein Eintrag gefunden wurde
    }

    return null; // Null zurückgeben, wenn kein Eintrag gefunden wurde
}

function updateTokensIfNeeded($mysqli, $username, $password)
{
    // Stelle sicher, dass die Session gestartet wurde
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Prüft, ob der Token für jeden aktiven Server erneuert werden muss oder nicht vorhanden ist
    $sql = "SELECT server_id, token, timestamp, url FROM ssi_trader.servers WHERE active = 1";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($serverInfo = $result->fetch_assoc()) {
            // Überprüft, ob kein Token vorhanden ist oder ob der Token älter als 12 Stunden ist
            $shouldUpdateToken = false; // Standardmäßig wird angenommen, dass der Token nicht aktualisiert werden muss

            // Überprüft, ob das Token-Feld leer ist
            if (empty($serverInfo['token'])) {
                $shouldUpdateToken = true;
            } else {
                // Überprüft, ob der Token älter als 12 Stunden ist
                $lastUpdateTimestamp = strtotime($serverInfo['timestamp']);
                if (time() - $lastUpdateTimestamp > 12 * 3600) {
                    $shouldUpdateToken = true;
                }
            }

            if ($shouldUpdateToken) {

                // Token generieren und in die Datenbank eintragen (getToken sollte dies tun)
                $newToken = getToken($serverInfo['server_id'], $serverInfo['url'], $username, $password, $mysqli);
                // Neuen Token in der Session speichern
                $_SESSION['token'][$serverInfo['server_id']] = $newToken;
            } else {
                // Vorhandenen Token in der Session speichern, falls nicht älter als 12 Stunden und vorhanden
                $_SESSION['token'][$serverInfo['server_id']] = $serverInfo['token'];
            }
        }
    } else {
        throw new Exception("Fehler beim Abrufen der Serverdaten aus der Datenbank.");
    }
}

function storeTokensInSession($mysqli)
{
    $_SESSION['token'] = array(); // Initialisiere das Array, um Fehler zu vermeiden.

    // SQL-Abfrage, um alle Tokens zusammen mit ihrer server_id zu erhalten.
    $query = "SELECT server_id, token FROM ssi_trader.servers";

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            // Speichere jeden Token im $_SESSION-Array unter Verwendung der server_id als Schlüssel.
            $_SESSION['token'][$row['server_id']] = $row['token'];
        }
        $result->free(); // Gib den Speicher des Result-Sets frei.
    } else {
        // Fehlerbehandlung, falls die Abfrage nicht erfolgreich war.
        error_log('Fehler beim Auslesen der Tokens aus der Datenbank: ' . $mysqli->error);
    }
}


function getBrokerClientList($connection)
{
    $brokerArray = [];

    // Adjusted query to include logic for 'real_account' check
    $query = "SELECT 
                broker.user, 
                CONCAT(IF(broker.real_account = 1, 'Live - ', 'Demo - '),
                broker.user,' - ',broker.title) AS broker_name,    
                CONCAT(clients.first_name, ' ', clients.last_name) AS client_name
              FROM 
                broker 
              LEFT JOIN 
                clients ON broker.user = clients.account
              ORDER BY 
                broker.real_account, broker.title, clients.first_name, clients.last_name";

    // Execute the query
    $result = $connection->query($query);

    // Check if the query was successful
    if ($result) {
        // Fetch all entries and populate the array
        while ($row = $result->fetch_assoc()) {
            // Use 'user' as the key and combine 'broker_name' with 'client_name' (if available) as the value
            $name = $row['broker_name'];
            if (!empty($row['client_name'])) {
                $name .= ' - ' . $row['client_name'];
            }
            $brokerArray[$row['user']] = $name;
        }

        // Free the result
        $result->free();
    } else {
        // Error handling
        echo "Fehler bei der Abfrage: " . $connection->error;
    }

    return $brokerArray;
}
