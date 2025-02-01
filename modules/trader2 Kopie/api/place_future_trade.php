<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

try {
    // Debug: Log raw input
    error_log("Raw POST input: " . file_get_contents('php://input'));
    
    // POST-Daten empfangen
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Debug: Log parsed data
    error_log("Parsed data: " . print_r($data, true));

    if (!$data) {
        throw new Exception("Invalid JSON input: " . json_last_error_msg());
    }

    // Validate required fields
    $required_fields = ['symbol', 'side', 'size', 'price', 'leverage'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // API credentials holen
    $query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials 
              WHERE platform = 'Bitget' AND is_active = 1 
              ORDER BY id DESC LIMIT 1";
    $result = $db->query($query);

    if (!$result || $result->num_rows === 0) {
        throw new Exception('No API credentials found');
    }

    $credentials = $result->fetch_assoc();

    // Trade ausführen
    $trading = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );

    $tradeResult = $trading->placeFutureTrade($data);
    error_log("Trade result: " . print_r($tradeResult, true));

    // Prepare success response with trade details
    $response = [
        'success' => true,
        'message' => 'Trade erfolgreich ausgeführt',
        'trade' => [
            'symbol' => $data['symbol'],
            'side' => $data['side'],
            'size' => $data['size'],
            'price' => $data['price'],
            'leverage' => $data['leverage'],
            'takeProfit' => $data['takeProfit'] ?? null,
            'stopLoss' => $data['stopLoss'] ?? null
        ],
        'result' => $tradeResult // Include the raw API response
    ];

    echo json_encode($response);

} catch (Exception $e) {
    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'json_error' => json_last_error_msg(),
            'post_data' => $_POST,
            'raw_input' => file_get_contents('php://input')
        ]
    ];
    error_log("Trade error: " . print_r($error, true));
    echo json_encode($error);
}