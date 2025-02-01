<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../classes/TradingParameters.php');

try {
    $tradingParams = TradingParameters::getInstance($db);
    
    $response = [
        'tp_percentage_long' => (float)$tradingParams->get('tp_percentage_long'),
        'sl_percentage_long' => (float)$tradingParams->get('sl_percentage_long'),
        'tp_percentage_short' => (float)$tradingParams->get('tp_percentage_short'),
        'sl_percentage_short' => (float)$tradingParams->get('sl_percentage_short'),
        'default_leverage' => (int)$tradingParams->get('default_leverage'),
        'default_trade_size' => (float)$tradingParams->get('default_trade_size'),
        'min_trade_size' => (float)$tradingParams->get('min_trade_size'),
        'max_trade_size' => (float)$tradingParams->get('max_trade_size')
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
