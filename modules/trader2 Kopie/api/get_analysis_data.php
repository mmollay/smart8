<?php
// api/get_analysis_data.php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');

try {
    // Zeitraum festlegen (letzte 24 Stunden)
    $timeframe = (time() - (24 * 3600)) * 1000;

    // Market Data abrufen
    $stmt = $db->prepare("
        SELECT 
            timestamp,
            price,
            rsi,
            ema20,
            ema50
        FROM market_data 
        WHERE symbol = ? AND timestamp >= ?
        ORDER BY timestamp ASC
    ");

    $symbol = 'ETHUSDT_UMCBL';
    $stmt->bind_param("si", $symbol, $timeframe);
    $stmt->execute();
    $marketData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Signale abrufen
    $stmt = $db->prepare("
        SELECT 
            timestamp,
            action,
            result
        FROM analysis_signals
        WHERE symbol = ? AND timestamp >= ?
        ORDER BY timestamp ASC
    ");

    $stmt->bind_param("si", $symbol, $timeframe);
    $stmt->execute();
    $signals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Signale in Market Data integrieren
    $signalMap = [];
    foreach ($signals as $signal) {
        $signalMap[$signal['timestamp']] = $signal['action'];
    }

    foreach ($marketData as &$data) {
        if (isset($signalMap[$data['timestamp']])) {
            $data['signal'] = $signalMap[$data['timestamp']];
        }
    }

    // Statistiken berechnen
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN result > 0 THEN 1 ELSE 0 END) as successful,
            AVG(CASE WHEN result IS NOT NULL THEN result ELSE 0 END) as avg_profit
        FROM analysis_signals
        WHERE symbol = ? AND timestamp >= ? AND result IS NOT NULL
    ");

    $stmt->bind_param("si", $symbol, $timeframe);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();

    // Verarbeite Statistiken sicher
    $totalSignals = intval($stats['total']);
    $successfulSignals = intval($stats['successful']);
    $avgProfit = floatval($stats['avg_profit']);

    $successRate = $totalSignals > 0 ? ($successfulSignals / $totalSignals) * 100 : 0;

    // Letzte Signale abrufen
    $stmt = $db->prepare("
        SELECT *
        FROM analysis_signals
        WHERE symbol = ?
        ORDER BY timestamp DESC
        LIMIT 10
    ");

    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $recentSignals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'marketData' => $marketData,
        'stats' => [
            'totalSignals' => $totalSignals,
            'successRate' => $successRate,
            'avgProfit' => $avgProfit
        ],
        'recentSignals' => $recentSignals
    ]);

} catch (Exception $e) {
    error_log('Error in get_analysis_data.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}