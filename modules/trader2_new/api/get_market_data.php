<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/DatabaseHandler.php');
require_once(__DIR__ . '/../classes/MarketData.php');

header('Content-Type: application/json');

try {
    // Parameter validieren
    $symbol = $_GET['symbol'] ?? 'ETHUSDT_UMCBL';
    $interval = $_GET['interval'] ?? '15m';
    $limit = min((int)($_GET['limit'] ?? 100), 1000);
    
    // Initialisiere Datenbankverbindung
    $db = new DatabaseHandler();
    
    // Initialisiere MarketData mit der Datenbankverbindung
    $marketData = new MarketData($db->getConnection());
    
    // Hole Daten basierend auf dem Anforderungstyp
    if (isset($_GET['type']) && $_GET['type'] === 'latest') {
        $data = $marketData->getLatestData($symbol);
    } else {
        $data = $marketData->getHistoricalData($symbol, $interval, $limit);
    }
    
    // Formatiere die Antwort
    echo json_encode([
        'success' => true,
        'symbol' => $symbol,
        'interval' => $interval,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log("API Fehler: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
