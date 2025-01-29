<?php
require '../t_config.php';
require '../bitget/bitget.php';

try {
    if (!isset($_POST['order_id'])) {
        throw new Exception("Order ID nicht angegeben");
    }

    $order_id = intval($_POST['order_id']);
    
    // Order aus der Datenbank holen
    $stmt = $db->prepare("
        SELECT o.*, ac.api_key, ac.api_secret, ac.api_passphrase 
        FROM orders o
        JOIN api_credentials ac ON o.id = ac.id
        WHERE o.id = ? AND ac.platform = 'bitget' AND ac.is_active = 1
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception("Order nicht gefunden");
    }

    // Bitget Client initialisieren
    $bitget = new Bitget($order['api_key'], $order['api_secret'], $order['api_passphrase']);
    
    // Position bei Bitget schließen
    $response = $bitget->close_position([
        'symbol' => $order['symbol'],
        'marginCoin' => 'USDT'
    ]);

    // Aktuelle Position abrufen für den Schlusskurs
    $position = $bitget->get_position([
        'symbol' => $order['symbol'],
        'marginCoin' => 'USDT'
    ]);

    // Order Status in der Datenbank aktualisieren
    $stmt = $db->prepare("
        UPDATE orders 
        SET status = 'closed',
            closing_price = ?,
            closed_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $closing_price = $position[0]['markPrice'];
    $stmt->bind_param("di", $closing_price, $order_id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Position erfolgreich geschlossen'
    ]);

} catch (Exception $e) {
    error_log("Fehler beim Schließen der Position: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
