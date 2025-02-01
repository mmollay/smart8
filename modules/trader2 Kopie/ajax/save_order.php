<?php
include __DIR__ . '/../t_config.php';
include __DIR__ . '/../bitget/bitget.php';

$response = ['success' => false, 'message' => ''];

try {
    // Pflichtfelder prüfen
    $required = ['user_id', 'parameter_model_id', 'symbol', 'side', 'position_size', 'entry_price', 'leverage'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        throw new Exception("Fehlende Pflichtfelder: " . implode(', ', $missing));
    }

    // API Keys des Users aus api_credentials laden
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

    // Order an Bitget senden
    $order_params = [
        'symbol' => $_POST['symbol'],
        'side' => $_POST['side'],
        'size' => $_POST['position_size'],
        'price' => $_POST['entry_price'],
        'leverage' => $_POST['leverage']
    ];

    // Take Profit und Stop Loss hinzufügen wenn gesetzt
    if (!empty($_POST['take_profit'])) {
        $order_params['takeProfit'] = $_POST['take_profit'];
    }
    if (!empty($_POST['stop_loss'])) {
        $order_params['stopLoss'] = $_POST['stop_loss'];
    }

    // Order bei Bitget platzieren
    $bitget_response = $bitget->place_order($order_params);

    if (!$bitget_response || isset($bitget_response['error'])) {
        throw new Exception("Bitget API Fehler: " . ($bitget_response['error'] ?? 'Unbekannter Fehler'));
    }

    // Stelle sicher, dass die benötigten Order IDs existieren
    $bitget_order_id = $bitget_response['data']['orderId'] ?? null;
    if (!$bitget_order_id) {
        throw new Exception("Keine Order ID von Bitget erhalten");
    }

    // Order in DB speichern
    $sql = "INSERT INTO orders (
        user_id,
        parameter_model_id,
        symbol,
        side,
        position_size,
        entry_price,
        take_profit,
        stop_loss,
        leverage,
        status,
        bitget_order_id,
        tp_order_id,
        sl_order_id,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'placed', ?, ?, ?, NOW())";

    $stmt = $db->prepare($sql);

    // Variablen für bind_param
    $user_id = intval($_POST['user_id']);
    $parameter_model_id = intval($_POST['parameter_model_id']);
    $symbol = $_POST['symbol'];
    $side = $_POST['side'];
    $position_size = floatval($_POST['position_size']);
    $entry_price = floatval($_POST['entry_price']);
    $take_profit = !empty($_POST['take_profit']) ? floatval($_POST['take_profit']) : 0;
    $stop_loss = !empty($_POST['stop_loss']) ? floatval($_POST['stop_loss']) : 0;
    $leverage = intval($_POST['leverage']);

    // Order IDs aus der API-Antwort extrahieren
    $tp_order_id = $bitget_response['data']['tpOrderId'] ?? null;
    $sl_order_id = $bitget_response['data']['slOrderId'] ?? null;

    // Temporäre Variablen für NULL-Werte
    $tp_order_id_temp = $tp_order_id ?? '';
    $sl_order_id_temp = $sl_order_id ?? '';

    $stmt->bind_param(
        "iissddddiiss",
        $user_id,
        $parameter_model_id,
        $symbol,
        $side,
        $position_size,
        $entry_price,
        $take_profit,
        $stop_loss,
        $leverage,
        $bitget_order_id,
        $tp_order_id_temp,
        $sl_order_id_temp
    );

    if ($stmt->execute()) {
        $order_id = $db->insert_id;
        $response = [
            'success' => true,
            'message' => 'Order erfolgreich bei Bitget platziert',
            'order_id' => $order_id,
            'bitget_order_id' => $bitget_order_id,
            'tp_order_id' => $tp_order_id,
            'sl_order_id' => $sl_order_id
        ];
    } else {
        throw new Exception("Fehler beim Speichern der Order: " . $stmt->error);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
