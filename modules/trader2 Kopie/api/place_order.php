<?php
include __DIR__ . '/../t_config.php';
include __DIR__ . '/../bitget/bitget.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Pflichtfelder prüfen
    $required = ['user_id', 'symbol', 'side', 'price', 'leverage', 'position_size', 'take_profit', 'stop_loss'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        throw new Exception("Fehlende Pflichtfelder: " . implode(', ', $missing));
    }

    // API Keys des Users laden
    $api_query = "SELECT api_key, api_secret, api_passphrase FROM api_credentials WHERE user_id = ? AND platform = 'bitget' AND is_active = 1";
    $stmt = $db->prepare($api_query);
    $stmt->bind_param("i", $_POST['user_id']);
    $stmt->execute();
    $api_result = $stmt->get_result();
    $api_data = $api_result->fetch_assoc();

    if (!$api_data) {
        throw new Exception("Keine aktiven Bitget API Keys für diesen User gefunden");
    }

    // Bitget Client initialisieren
    $bitget = new Bitget(
        $api_data['api_key'],
        $api_data['api_secret'],
        $api_data['api_passphrase']
    );

    // Order Parameter vorbereiten
    $order_params = [
        'symbol' => $_POST['symbol'],
        'side' => $_POST['side'],
        'size' => $_POST['position_size'],
        'price' => $_POST['price'],
        'leverage' => $_POST['leverage'],
        'takeProfit' => $_POST['take_profit'],
        'stopLoss' => $_POST['stop_loss']
    ];

    // Test Mode - Keine echte Order
    if (isset($_POST['test_mode']) && $_POST['test_mode'] == 1) {
        $response['success'] = true;
        $response['message'] = 'Test Order erfolgreich simuliert';
        $response['data'] = [
            'orderId' => 'TEST_' . time(),
            'symbol' => $order_params['symbol'],
            'side' => $order_params['side'],
            'size' => $order_params['size'],
            'price' => $order_params['price'],
            'leverage' => $order_params['leverage'],
            'takeProfitPrice' => $order_params['takeProfit'],
            'stopLossPrice' => $order_params['stopLoss']
        ];
    }
    // Live Mode - Echte Order
    else {
        $bitget_response = $bitget->place_order($order_params);

        if (isset($bitget_response['error'])) {
            throw new Exception("Bitget API Fehler: " . $bitget_response['error']);
        }

        // Order in DB speichern
        $sql = "INSERT INTO orders (
            user_id,
            symbol,
            side,
            position_size,
            entry_price,
            take_profit,
            stop_loss,
            leverage,
            status,
            bitget_order_id,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'placed', ?, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->bind_param(
            "issdddsis",
            $_POST['user_id'],
            $_POST['symbol'],
            $_POST['side'],
            $_POST['position_size'],
            $_POST['price'],
            $_POST['take_profit'],
            $_POST['stop_loss'],
            $_POST['leverage'],
            $bitget_response['data']['orderId']
        );
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Order erfolgreich platziert';
        $response['data'] = $bitget_response['data'];
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
