<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

header('Content-Type: application/json');

// Empfange JSON-Daten
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'error' => 'Ungültige Eingabedaten'
    ]);
    exit;
}

try {
    // Hole API-Credentials für den Benutzer
    $query = "SELECT api_key, api_secret, api_passphrase 
              FROM api_credentials 
              WHERE user_id = ? 
              AND platform = 'bitget' 
              AND is_active = 1 
              ORDER BY last_used DESC 
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $input['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $credentials = $result->fetch_assoc();

    if (!$credentials) {
        throw new Exception('Keine aktiven BitGet API-Zugangsdaten gefunden');
    }

    // Bereite die Order-Daten vor
    $orderData = [
        'user_id' => $input['userId'],
        'parameter_model_id' => $input['modelId'],
        'symbol' => $input['symbol'] . '_UMCBL', // BitGet Format
        'side' => strtolower($input['action']),
        'position_size' => $input['positionSize'],
        'entry_price' => $input['currentPrice'],
        'take_profit' => $input['takeProfit'],
        'stop_loss' => $input['stopLoss'],
        'leverage' => $input['leverage'],
        'status' => 'pending'
    ];
    
    // Füge die Order in die Datenbank ein
    $query = "INSERT INTO orders (
                user_id, parameter_model_id, symbol, side, 
                position_size, entry_price, take_profit, stop_loss, 
                leverage, status, created_at
              ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?, 
                ?, 'pending', NOW()
              )";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param(
        'iissddddi',
        $orderData['user_id'],
        $orderData['parameter_model_id'],
        $orderData['symbol'],
        $orderData['side'],
        $orderData['position_size'],
        $orderData['entry_price'],
        $orderData['take_profit'],
        $orderData['stop_loss'],
        $orderData['leverage']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Fehler beim Speichern der Order in der Datenbank');
    }
    
    $orderId = $db->insert_id;
    
    // Initialisiere BitGet Trading
    $trading = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    // Platziere den Trade
    $tradeData = [
        'symbol' => $orderData['symbol'],
        'side' => $orderData['side'],
        'size' => $orderData['position_size'],
        'price' => $orderData['entry_price'],
        'leverage' => $orderData['leverage'],
        'takeProfit' => $orderData['take_profit'],
        'stopLoss' => $orderData['stop_loss']
    ];
    
    $tradeResult = $trading->placeFutureTrade($tradeData);
    
    // Aktualisiere die Order mit der BitGet Order ID
    if (isset($tradeResult['orderId'])) {
        $updateQuery = "UPDATE orders 
                       SET bitget_order_id = ?, 
                           status = 'placed',
                           updated_at = NOW() 
                       WHERE id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param('si', $tradeResult['orderId'], $orderId);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
        'bitgetOrderId' => $tradeResult['orderId'] ?? null,
        'message' => 'Handelssignal erfolgreich verarbeitet und Trade platziert'
    ]);
    
} catch (Exception $e) {
    // Wenn eine Order bereits erstellt wurde, aktualisiere ihren Status
    if (isset($orderId)) {
        $updateQuery = "UPDATE orders 
                       SET status = 'rejected',
                           updated_at = NOW() 
                       WHERE id = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'sql_error' => $db->error ?? null,
            'input_data' => $input
        ]
    ]);
}
