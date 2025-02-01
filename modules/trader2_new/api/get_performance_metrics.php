<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/DatabaseHandler.php');
require_once(__DIR__ . '/../classes/TradingMetrics.php');

header('Content-Type: application/json');

try {
    $db = new DatabaseHandler();
    $metrics = new TradingMetrics($db);
    
    // Hole die Performance-Metriken
    $performanceData = $metrics->getPerformanceMetrics();
    
    echo json_encode([
        'success' => true,
        'profit_factor' => $performanceData['profit_factor'],
        'win_rate' => $performanceData['win_rate'],
        'sharpe_ratio' => $performanceData['sharpe_ratio'],
        'max_drawdown' => $performanceData['max_drawdown']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
