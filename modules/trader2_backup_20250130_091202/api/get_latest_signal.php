<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');

try {
    // Letztes Signal und zugehörige Market Data abrufen
    $stmt = $db->prepare("
        SELECT 
            s.*,
            m.rsi
        FROM analysis_signals s
        LEFT JOIN market_data m ON m.timestamp = s.timestamp AND m.symbol = s.symbol
        WHERE s.symbol = ?
        ORDER BY s.timestamp DESC
        LIMIT 1
    ");

    $symbol = 'ETHUSDT_UMCBL';
    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        // Numerische Werte formatieren
        $result['entry_price'] = floatval($result['entry_price']);
        $result['take_profit'] = floatval($result['tp_price']);  // tp_price zu take_profit
        $result['stop_loss'] = floatval($result['sl_price']);    // sl_price zu stop_loss
        $result['confidence'] = intval($result['confidence']);
        $result['rsi'] = $result['rsi'] ? floatval($result['rsi']) : null;
        
        // Timestamp in Millisekunden
        $result['timestamp'] = strtotime($result['timestamp']) * 1000;

        // Nicht mehr benötigte Felder entfernen
        unset($result['tp_price']);
        unset($result['sl_price']);

        echo json_encode([
            'success' => true,
            'signal' => $result
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'signal' => null
        ]);
    }

} catch (Exception $e) {
    error_log('Error in get_latest_signal.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
