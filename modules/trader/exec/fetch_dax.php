<?php


// CREATE TABLE IF NOT EXISTS stock_index (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
//     symbol VARCHAR(50),
//     ask DECIMAL(10, 2),
//     bid DECIMAL(10, 2),
//     volume INT,
//     INDEX (timestamp),
//     INDEX (symbol)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

$host = '85.215.176.20';
$port = '6969';
$path = '/ws';
$logFile = 'websocket_ger30.log';

// WebSocket-Handshake vorbereiten
$header = "GET " . $path . " HTTP/1.1\r\n"
    . "Host: " . $host . ":" . $port . "\r\n"
    . "Upgrade: websocket\r\n"
    . "Connection: Upgrade\r\n"
    . "Sec-WebSocket-Key: " . base64_encode(openssl_random_pseudo_bytes(16)) . "\r\n"
    . "Sec-WebSocket-Version: 13\r\n"
    . "\r\n";

// Socket-Verbindung öffnen
$socket = fsockopen($host, $port, $errno, $errstr, 2);
if (!$socket) {
    die("Fehler beim Verbinden: $errstr ($errno)\n");
}

// WebSocket-Handshake senden
fwrite($socket, $header);

// Antwort vom Server lesen (Handshake-Antwort und evtl. weitere Daten)
$response = fread($socket, 1500);

// Überprüfen, ob der Handshake erfolgreich war
//if (strpos($response, '258EAFA5-E914-47DA-95CA-C5AB0DC85B11') === false) {
//   die("WebSocket-Handshake fehlgeschlagen.");
//}

echo "Verbunden und bereit, Nachrichten zu empfangen\n";

// Schleife, um Nachrichten vom Server zu lesen
while (!feof($socket)) {
    $data = fread($socket, 1024);

    // Daten auf das Symbol "GER30" prüfen
    if (strpos($data, '"SYMBOL":"GER30"') !== false) {
        // Daten in ein Logfile schreiben
        file_put_contents($logFile, $data . PHP_EOL, FILE_APPEND);
        echo "Empfangene Daten (GER30): $data\n";
    }
}

// Socket schließen
fclose($socket);