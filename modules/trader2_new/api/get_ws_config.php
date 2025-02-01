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
    // Initialisiere Datenbankverbindung
    $db = DatabaseHandler::getInstance();
    
    // Hole aktive API-Konfiguration
    $query = "SELECT api_key, api_secret, passphrase 
              FROM " . DB_NAME . ".api_config 
              WHERE name = 'bitget' 
              AND is_active = 1 
              LIMIT 1";
              
    $stmt = $db->getConnection()->prepare($query);
    if (!$stmt) {
        throw new Exception('Datenbankfehler: ' . $db->getConnection()->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine aktive API-Konfiguration gefunden');
    }
    
    $config = $result->fetch_assoc();
    
    // Validiere API-Konfiguration
    if (empty($config['api_key']) || empty($config['api_secret']) || empty($config['passphrase'])) {
        throw new Exception('Unvollständige API-Konfiguration');
    }
    
    // Hole Standard-Symbol
    $symbol = $_GET['symbol'] ?? 'ETHUSDT_UMCBL';
    
    // Validiere Symbol
    if (!preg_match('/^[A-Z0-9_]+$/', $symbol)) {
        throw new Exception('Ungültiges Symbol Format');
    }
    
    // BitGet WebSocket Authentifizierung
    $timestamp = (string)(time() * 1000);
    
    // 1. Erstelle den verschlüsselten Passphrase
    // Der Passphrase wird direkt verwendet, keine zusätzliche Verschlüsselung
    $passphrase = $config['passphrase'];
    
    // 2. Erstelle den PreHash-String für die Signatur
    // Format: timestamp + method + requestPath
    $preHash = $timestamp . 'GET' . '/mix/v1/user/verify';
    
    // 3. Erstelle die Signatur
    $sign = base64_encode(hash_hmac('sha256', $preHash, $config['api_secret'], true));
    
    // Debug-Log
    error_log("WebSocket Authentifizierung:");
    error_log("API Key: " . $config['api_key']);
    error_log("Timestamp: " . $timestamp);
    error_log("PreHash: " . $preHash);
    error_log("Sign: " . $sign);
    
    // Erstelle WebSocket-Konfiguration
    $wsConfig = [
        'url' => 'wss://ws.bitget.com/mix/v1/stream',
        'subscription' => [
            'op' => 'subscribe',
            'args' => [
                [
                    'instType' => 'UMCBL',
                    'channel' => 'ticker',
                    'instId' => $symbol
                ],
                [
                    'instType' => 'UMCBL',
                    'channel' => 'candle1m',
                    'instId' => $symbol
                ]
            ]
        ],
        'auth' => [
            'op' => 'login',
            'args' => [[
                'apiKey' => $config['api_key'],
                'passphrase' => $passphrase,
                'timestamp' => $timestamp,
                'sign' => $sign
            ]]
        ],
        'pingInterval' => 20000,
        'reconnectInterval' => 5000
    ];
    
    // Log für Debugging
    error_log("WebSocket Konfiguration erstellt für Symbol: " . $symbol);
    error_log("Auth-Daten: " . json_encode($wsConfig['auth'], JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'config' => $wsConfig,
        'debug' => [
            'timestamp' => $timestamp,
            'preHash' => $preHash,
            'sign' => $sign
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("WebSocket Konfigurationsfehler: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
