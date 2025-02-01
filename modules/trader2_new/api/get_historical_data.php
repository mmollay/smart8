<?php
// Absolute Pfade für Includes
$moduleRoot = realpath(__DIR__ . '/..');
require_once($moduleRoot . '/config/t_config.php');
require_once($moduleRoot . '/classes/DatabaseHandler.php');

header('Content-Type: application/json');

// Aktiviere Error-Reporting für Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Parameter validieren
    $symbol = $_GET['symbol'] ?? DEFAULT_SYMBOL;
    $interval = $_GET['interval'] ?? '15m';
    
    // Validiere Symbol
    if (!preg_match('/^[A-Z0-9_]+$/', $symbol)) {
        throw new Exception('Ungültiges Symbol Format');
    }
    
    // Validiere Interval
    $validIntervals = ['1m', '5m', '15m', '1H', '4H', '1D'];
    $intervalMap = [
        '1m' => '1m',
        '5m' => '5m',
        '15m' => '15m',
        '1h' => '1H',
        '4h' => '4H',
        '1d' => '1D'
    ];
    
    // Konvertiere Interval in BitGet-Format
    $bitgetInterval = $intervalMap[strtolower($interval)] ?? '15m';
    
    // Hole API-Konfiguration aus der Datenbank
    $db = DatabaseHandler::getInstance();
    $query = "SELECT api_key, api_secret, passphrase 
              FROM " . DB_NAME . ".api_config 
              WHERE name = 'bitget' 
              AND is_active = 1 
              LIMIT 1";
              
    $stmt = $db->getConnection()->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine aktive API-Konfiguration gefunden');
    }
    
    $config = $result->fetch_assoc();
    
    // BitGet API Endpunkt für Klines/Candlesticks
    $endpoint = 'https://api.bitget.com/api/mix/v1/market/history-candles';
    
    // Aktuelle Zeit in Millisekunden
    $timestamp = time() * 1000;
    $startTime = $timestamp - (100 * 60 * 60 * 1000); // 100 Stunden zurück für mehr Daten
    
    // Parameter für die API
    $params = [
        'symbol' => $symbol,
        'granularity' => $bitgetInterval,
        'startTime' => $startTime,
        'endTime' => $timestamp,
        'limit' => 100
    ];
    
    // Erstelle die URL mit Query-Parametern
    $url = $endpoint . '?' . http_build_query($params);
    
    // Generiere Signatur
    $method = 'GET';
    $requestPath = '/api/mix/v1/market/history-candles?' . http_build_query($params);
    $signStr = $timestamp . $method . $requestPath;
    $sign = base64_encode(hash_hmac('sha256', $signStr, $config['api_secret'], true));
    
    // Debug-Log
    error_log("Request Details:");
    error_log("URL: " . $url);
    error_log("Timestamp: " . $timestamp);
    error_log("Sign String: " . $signStr);
    
    // Initialisiere cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'ACCESS-KEY: ' . $config['api_key'],
            'ACCESS-SIGN: ' . $sign,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $config['passphrase'],
            'Content-Type: application/json',
            'locale: de-DE'
        ]
    ]);
    
    // Führe Request aus
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Debug-Log
    error_log("API Response Code: " . $httpCode);
    error_log("API Response: " . $response);
    
    if ($httpCode !== 200) {
        throw new Exception('API Fehler: HTTP ' . $httpCode . ' - Response: ' . $response);
    }
    
    if (curl_errno($ch)) {
        throw new Exception('cURL Fehler: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    // Verarbeite Response
    $data = json_decode($response, true);
    
    if (!$data) {
        throw new Exception('Ungültige JSON-Antwort: ' . $response);
    }
    
    // Prüfe ob es ein Array ist
    if (!is_array($data)) {
        throw new Exception('Ungültiges Datenformat: Erwarte Array');
    }
    
    // Formatiere Daten für ApexCharts
    $candles = array_map(function($candle) {
        if (count($candle) < 5) {
            throw new Exception('Ungültiges Kerzenformat');
        }
        return [
            'x' => intval($candle[0]), // timestamp
            'y' => [
                floatval($candle[1]), // open
                floatval($candle[2]), // high
                floatval($candle[3]), // low
                floatval($candle[4])  // close
            ]
        ];
    }, $data);
    
    // Debug-Log
    error_log("Historische Daten geladen für Symbol: " . $symbol);
    error_log("Anzahl Kerzen: " . count($candles));
    
    echo json_encode([
        'success' => true,
        'candles' => array_reverse($candles) // Umkehren für chronologische Reihenfolge
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Fehler beim Laden der historischen Daten: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
