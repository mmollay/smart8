<?php
require_once(__DIR__ . '/../t_config.php');

// Debug: Zeige Tabellenstruktur
$structure_query = "SHOW COLUMNS FROM analysis_signals";
$structure_result = $db->query($structure_query);
$columns = [];
if ($structure_result) {
    while ($row = $structure_result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Get latest signal with different symbol formats
$signal_query = "SELECT * FROM analysis_signals 
                WHERE symbol IN ('ETHUSDT', 'ETH/USDT', 'ETHUSDT_UMCBL')
                ORDER BY created_at DESC 
                LIMIT 1";
$signal_result = $db->query($signal_query);

if ($signal_result && $signal = $signal_result->fetch_assoc()) {
    // Debug: Zeige alle Werte
    $debug_values = array_map(function($value) {
        return $value === null ? "NULL" : $value;
    }, $signal);
    
    // Get signal from action column
    $signal_type = $signal['action'] ?? null;
    
    // Parse reasoning
    $reasoning = json_decode($signal['reasoning'] ?? '[]', true);
    $reason = is_array($reasoning) ? implode("\n", $reasoning) : '';
    
    // Calculate confidence (already in correct format)
    $confidence = floatval($signal['confidence'] ?? 0);
    
    // Get timestamp
    $timestamp = $signal['created_at'];
    
    // Debug: Zeige verarbeitete Werte
    $processed = [
        'signal_type' => $signal_type,
        'confidence' => $confidence,
        'timestamp' => $timestamp,
        'reason' => $reason,
        'entry_price' => $signal['entry_price'] ?? null,
        'tp_price' => $signal['tp_price'] ?? null,
        'sl_price' => $signal['sl_price'] ?? null
    ];
    
    echo json_encode([
        'success' => true,
        'analysis' => [
            'signal' => $signal_type,
            'confidence' => $confidence,
            'timestamp' => $timestamp,
            'reason' => $reason,
            'entry_price' => $signal['entry_price'] ?? null,
            'tp_price' => $signal['tp_price'] ?? null,
            'sl_price' => $signal['sl_price'] ?? null
        ],
        'debug' => [
            'raw_values' => $debug_values,
            'processed_values' => $processed,
            'columns' => $columns,
            'query' => $signal_query
        ]
    ]);
} else {
    // Debug-Info ausgeben
    $debug_query = "SELECT DISTINCT symbol FROM analysis_signals ORDER BY symbol";
    $debug_result = $db->query($debug_query);
    $available_symbols = [];
    while ($row = $debug_result->fetch_assoc()) {
        $available_symbols[] = $row['symbol'];
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'No signal found',
        'debug' => [
            'available_symbols' => $available_symbols,
            'columns' => $columns,
            'query_error' => $db->error,
            'query' => $signal_query
        ]
    ]);
}
