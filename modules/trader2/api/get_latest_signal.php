<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');

try {
    error_log('Fetching latest signal...');
    
    // Letztes Signal und zugehörige Market Data abrufen
    $stmt = $db->prepare("
        SELECT 
            s.timestamp,
            s.action as side,
            m.price as entry_price,
            m.rsi,
            CASE 
                WHEN s.action = 'buy' THEN m.price * 1.02
                ELSE m.price * 0.98
            END as take_profit,
            CASE 
                WHEN s.action = 'buy' THEN m.price * 0.98
                ELSE m.price * 1.02
            END as stop_loss,
            CASE 
                WHEN ABS(m.rsi - 50) > 20 THEN 90
                WHEN ABS(m.rsi - 50) > 15 THEN 75
                WHEN ABS(m.rsi - 50) > 10 THEN 60
                ELSE 50
            END as confidence
        FROM analysis_signals s
        JOIN market_data m ON m.timestamp = s.timestamp AND m.symbol = s.symbol
        WHERE s.symbol = ?
        ORDER BY s.timestamp DESC
        LIMIT 1
    ");

    if (!$stmt) {
        error_log('MySQL Error: ' . $db->error);
        throw new Exception('Database query preparation failed');
    }

    $symbol = 'ETHUSDT_UMCBL';
    if (!$stmt->bind_param("s", $symbol)) {
        error_log('MySQL Bind Error: ' . $stmt->error);
        throw new Exception('Parameter binding failed');
    }

    if (!$stmt->execute()) {
        error_log('MySQL Execute Error: ' . $stmt->error);
        throw new Exception('Query execution failed');
    }

    $result = $stmt->get_result()->fetch_assoc();
    
    error_log('Query result: ' . print_r($result, true));

    if ($result) {
        // Zeitstempel in Millisekunden für JavaScript
        $result['timestamp'] = strtotime($result['timestamp']) * 1000;
        
        // Numerische Werte formatieren
        $result['entry_price'] = floatval($result['entry_price']);
        $result['take_profit'] = floatval($result['take_profit']);
        $result['stop_loss'] = floatval($result['stop_loss']);
        $result['confidence'] = intval($result['confidence']);
        
        error_log('Sending signal: ' . print_r($result, true));
        
        echo json_encode([
            'success' => true,
            'signal' => $result
        ]);
    } else {
        error_log('No signal found');
        echo json_encode([
            'success' => true,
            'signal' => null
        ]);
    }

} catch (Exception $e) {
    error_log('Error in get_latest_signal.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Abrufen des Signals: ' . $e->getMessage()
    ]);
}
