<?php
require 't_config.php';
require 'bitget/bitget.php';

try {
    // Test User ID und Parameter Model ID
    $user_id = 1; // Ersetzen Sie dies mit Ihrer User ID
    $parameter_model_id = 1; // Ersetzen Sie dies mit Ihrer Parameter Model ID

    // API Credentials laden
    $stmt = $db->prepare("SELECT api_key, api_secret, api_passphrase FROM api_credentials WHERE user_id = ? AND platform = 'bitget' AND is_active = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $api_data = $result->fetch_assoc();

    if (!$api_data) {
        throw new Exception("Keine API Credentials gefunden");
    }

    // Bitget Client initialisieren
    $bitget = new Bitget($api_data['api_key'], $api_data['api_secret'], $api_data['api_passphrase']);

    // Aktuelle ETHUSDT Price holen
    $current_price = 2200; // Beispielpreis, sollte eigentlich von der API geholt werden

    // Order Parameter
    $params = [
        'symbol' => 'ETHUSDT',
        'side' => 'buy',
        'size' => 0.01,
        'price' => $current_price,
        'leverage' => 10,
        'takeProfit' => $current_price * 1.05, // +5%
        'stopLoss' => $current_price * 0.98    // -2%
    ];

    // Order platzieren
    $order_ids = $bitget->place_order($params);

    if (isset($order_ids['error'])) {
        throw new Exception($order_ids['error']);
    }

    // Variablen für bind_param vorbereiten
    $symbol = $params['symbol'];
    $side = $params['side'];
    $size = $params['size'];
    $price = $params['price'];
    $take_profit = $params['takeProfit'];
    $stop_loss = $params['stopLoss'];
    $leverage = $params['leverage'];
    $main_order_id = $order_ids['main'];
    $tp_order_id = $order_ids['tp'] ?? null;
    $sl_order_id = $order_ids['sl'] ?? null;
    $status = 'pending';

    // Order in DB speichern
    $stmt = $db->prepare("
        INSERT INTO orders (
            user_id,
            parameter_model_id,
            symbol, 
            side, 
            position_size, 
            entry_price, 
            take_profit, 
            stop_loss, 
            leverage,
            bitget_order_id,
            tp_order_id,
            sl_order_id,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iissddddiisss",
        $user_id,
        $parameter_model_id,
        $symbol,
        $side,
        $size,
        $price,
        $take_profit,
        $stop_loss,
        $leverage,
        $main_order_id,
        $tp_order_id,
        $sl_order_id,
        $status
    );

    $stmt->execute();
    $order_db_id = $db->insert_id;

    // WebSocket Monitor starten
    $cmd = sprintf(
        'php "%s/websocket/start_monitor.php" %d %d > /dev/null 2>&1 & echo $!',
        __DIR__,
        $user_id,
        $order_db_id
    );
    exec($cmd, $output);

    echo "Order erfolgreich platziert!\n";
    echo "Order IDs:\n";
    echo "- Main: " . $order_ids['main'] . "\n";
    echo "- TP: " . ($order_ids['tp'] ?? 'N/A') . "\n";
    echo "- SL: " . ($order_ids['sl'] ?? 'N/A') . "\n";
    echo "DB ID: " . $order_db_id . "\n";
    echo "Monitor PID: " . $output[0] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Stack trace für debugging
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
