<?php
session_start();

//http://deineDomain.de/fetch_stock.php?token=52a36a36e2e6da849685b71f466dde56
//http://localhost/smart7/ssi_trader/exec/fetch_stock.php?token=52a36a36e2e6da849685b71f466dde56

// für cronjob  
//* * * * * php /var/www/ssi/smart7/ssi_trader/exec/fetch_stock.php 52a36a36e2e6da849685b71f466dde56

define('SECRET_TOKEN', '52a36a36e2e6da849685b71f466dde56');
function validateToken($token)
{
    return $token === SECRET_TOKEN;
}

// Überprüfe, ob das Skript von der Kommandozeile ausgeführt wird
if (php_sapi_name() === 'cli') {
    $token = $argc > 1 ? $argv[1] : null;
} else {
    $token = isset($_GET['token']) ? $_GET['token'] : null;
}

if (!validateToken($token)) {
    die("Unbefugter Zugriff! Falscher oder fehlender Token.");
}

require_once (__DIR__ . "/../functions.inc.php");
require_once (__DIR__ . "/../t_config.php");

// Verbindung zur Datenbank
$mysqli = mysqli_connect($cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'], $cfg_mysql['db']);
if (!$mysqli) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}


$url = 'http://85.215.176.20:8080/emaPrint';

// Schleife, die 5 Mal durchlaufen wird
for ($i = 0; $i < 3; $i++) {
    $jsonString = sendCurlRequest($url);
    $jsonArray = json_decode($jsonString, true);

    print_r($jsonArray) . "\n";
    echo "test";
    $query = "INSERT INTO `stocks_data` (`buy`, `sell`, `time`, `price`) VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE `buy` = VALUES(`buy`), `sell` = VALUES(`sell`)";

    $stmt = mysqli_prepare($mysqli, $query);
    if (!$stmt) {
        die("Fehler bei der Vorbereitung des Statements: " . mysqli_error($mysqli));
    }

    mysqli_stmt_bind_param($stmt, "iiss", $jsonArray['buy'], $jsonArray['sell'], $jsonArray['time'], $jsonArray['price']);
    if (mysqli_stmt_execute($stmt)) {
        echo "Datensatz erfolgreich eingefügt oder aktualisiert.\n";
    } else {
        echo "Fehler: " . mysqli_stmt_error($stmt) . "\n";
    }

    mysqli_stmt_close($stmt);

    // Warte 10 Sekunden vor dem nächsten Durchlauf
    sleep(10);
}

mysqli_close($mysqli);
