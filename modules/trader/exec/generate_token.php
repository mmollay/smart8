<?php
session_start();
header('Content-Type: application/json');

require_once (__DIR__ . "/../t_config.php");

// Pfad zur Datei definieren, in der die Session-Daten gespeichert werden
$sessionDataFile = __DIR__ . '/session_data.txt';

// Überprüfen, ob das Skript über CLI aufgerufen wird
if (php_sapi_name() === 'cli') {
    // Überprüfen, ob die Datei existiert
    if (file_exists($sessionDataFile)) {
        // Session-Daten aus der Datei lesen
        $sessionData = json_decode(file_get_contents($sessionDataFile), true);

        // Session-Daten wiederherstellen
        $_SESSION['token'] = $sessionData['token'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Session-Daten nicht gefunden.']);
        exit;
    }
}

try {
    $serverIps = getAllServerIps($mysqli);
    $tokensGenerated = false; // Flag, um den Erfolg der Operation zu verfolgen

    foreach ($serverIps as $serverInfo) {
        $serverIp = $serverInfo['url'];
        $server_id = $serverInfo['server_id'];
        $get_token[$server_id] = getToken($server_id, $serverIp, $username, $password, $mysqli);

        if ($get_token[$server_id]) {
            $_SESSION['token'][$server_id] = $get_token[$server_id];
            $tokensGenerated = true; // Mindestens ein Token wurde erfolgreich generiert
        }

    }

    if ($tokensGenerated) {
        // Speichere die Session-Token in der Datei
        $sessionData = ['token' => $_SESSION['token']];
        file_put_contents($sessionDataFile, json_encode($sessionData));

        echo json_encode(['success' => true, 'message' => 'Token generated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to generate any tokens.']);
    }
} catch (Exception $e) {
    // Im Falle eines Fehlers, sende eine Fehlermeldung
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}