<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Core/Database.php';
require_once __DIR__ . '/../classes/MarketData.php';
require_once __DIR__ . '/../classes/Backtesting.php';
require_once __DIR__ . '/../config/api_config.php';

use Smart\Core\Database;

header('Content-Type: application/json');

try {
    // Lese POST-Daten
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Ungültige Anfrage-Daten');
    }
    
    // Validiere Parameter
    $required = ['symbol', 'interval', 'period', 'initialBalance', 'riskPerTrade', 'stopLoss', 'takeProfit', 'feeRate'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Fehlendes Feld: $field");
        }
    }
    
    // Initialisiere Klassen
    $config = [
        'host' => '127.0.0.1',
        'username' => 'smart',
        'password' => 'Eiddswwenph21;',
        'database' => 'ssi_trader2'
    ];
    
    $db = Database::getInstance($config);
    $marketData = new MarketData($db);
    $backtesting = new Backtesting($db, $marketData, [
        'initialBalance' => $data['initialBalance'],
        'feeRate' => $data['feeRate'],
        'stopLoss' => $data['stopLoss'],
        'takeProfit' => $data['takeProfit'],
        'riskPerTrade' => $data['riskPerTrade']
    ]);
    
    // Führe Backtest aus
    $results = $backtesting->runBacktest([
        'symbol' => $data['symbol'],
        'interval' => $data['interval'],
        'period' => $data['period']
    ]);
    
    // Sende Ergebnisse zurück
    echo json_encode([
        'success' => true,
        'metrics' => $results['metrics'],
        'equity_curve' => $results['equity_curve']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
