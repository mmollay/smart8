<?php
// test_api.php
require_once(__DIR__ . '/../t_config.php');
require_once('BitgetTrading.php');

// API Credentials aus der Datenbank
$query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
          WHERE platform = 'Bitget' AND is_active = 1 
          ORDER BY id DESC LIMIT 1";
$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    die('Keine API Credentials gefunden');
}

$credentials = $result->fetch_assoc();

// BitgetTrading Instanz erstellen
$trading = new BitgetTrading(
    $credentials['api_key'],
    $credentials['api_secret'],
    $credentials['api_passphrase']
);

// Test der API-Verbindung
try {
    // Klines Endpoint Test
    echo "Teste Klines Endpoint...\n";
    $timestamp = time() * 1000;
    $method = 'GET';
    $requestPath = '/api/mix/v1/market/candles';

    // Korrekte Parameter gemäß API-Spezifikation
    $params = [
        'symbol' => 'ETHUSDT_UMCBL',
        'granularity' => '15m',
        'endTime' => $timestamp,
        'startTime' => $timestamp - (86400 * 1000), // 24 Stunden zurück
        'limit' => '100'
    ];

    // Query-String erstellen
    $queryString = http_build_query($params);
    $fullPath = $requestPath . '?' . $queryString;

    // Signature erstellen
    $message = $timestamp . $method . $fullPath;
    $signature = base64_encode(hash_hmac('sha256', $message, $credentials['api_secret'], true));

    echo "Request URL: https://api.bitget.com" . $fullPath . "\n";
    echo "Timestamp: " . $timestamp . "\n";

    // CURL Request
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.bitget.com" . $fullPath,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'ACCESS-KEY: ' . $credentials['api_key'],
            'ACCESS-SIGN: ' . $signature,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $credentials['api_passphrase'],
            'Content-Type: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "HTTP Status Code: " . $httpCode . "\n";
    echo "Raw Response: " . $response . "\n";

    if (curl_errno($ch)) {
        echo "CURL Error: " . curl_error($ch) . "\n";
    }

    curl_close($ch);

    // Response decodieren und analysieren
    $data = json_decode($response, true);
    if ($data === null) {
        echo "JSON Decode Error: " . json_last_error_msg() . "\n";
    } else {
        echo "Decoded Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack Trace: " . $e->getTraceAsString() . "\n";
}