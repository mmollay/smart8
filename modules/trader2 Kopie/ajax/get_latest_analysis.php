<?php
require_once(__DIR__ . '/../t_config.php');

header('Content-Type: application/json');

// Get symbol from request or use default
$symbol = $_GET['symbol'] ?? 'ETHUSDT';

// Get latest signal for the specific symbol
$signal_query = "SELECT * FROM analysis_signals 
                WHERE symbol = ? 
                ORDER BY timestamp DESC, created_at DESC 
                LIMIT 1";

$stmt = $db->prepare($signal_query);
$stmt->bind_param('s', $symbol);
$stmt->execute();
$signal_result = $stmt->get_result();

if ($signal_result && $signal = $signal_result->fetch_assoc()) {
    // Parse reasoning if exists
    $reasoning = json_decode($signal['reasoning'] ?? '[]', true);
    $reason = is_array($reasoning) ? implode("\n", $reasoning) : '';
    
    // Format the response
    echo json_encode([
        'success' => true,
        'analysis' => [
            'signal' => $signal['action'],
            'confidence' => floatval($signal['confidence']),
            'timestamp' => $signal['timestamp'],
            'created_at' => $signal['created_at'],
            'entry_price' => floatval($signal['entry_price']),
            'tp_price' => floatval($signal['tp_price']),
            'sl_price' => floatval($signal['sl_price']),
            'reason' => $reason
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No signal found for symbol: ' . $symbol,
        'debug' => [
            'symbol' => $symbol,
            'query_error' => $db->error
        ]
    ]);
}
