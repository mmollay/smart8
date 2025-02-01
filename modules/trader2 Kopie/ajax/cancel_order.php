<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget.php');

// Debug: Zeige alle POST-Daten
var_dump($_POST);

if (!isset($_POST['order_id'])) {
    die("Error: Order ID nicht angegeben");
}

$order_id = intval($_POST['order_id']);
echo "Processing order_id: " . $order_id . "\n";

// Erst die Order holen
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
if (!$stmt) {
    die("Database error (prepare orders): " . $db->error);
}

$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) {
    die("Database error (execute orders): " . $stmt->error);
}

$order = $stmt->get_result()->fetch_assoc();
echo "Order data: ";
var_dump($order);

if (!$order) {
    die("Error: Order nicht gefunden für ID: " . $order_id);
}

// Dann die API Credentials holen
$stmt = $db->prepare("SELECT * FROM api_credentials WHERE id = 1 AND platform = 'bitget' AND is_active = 1");
if (!$stmt) {
    die("Database error (prepare credentials): " . $db->error);
}

if (!$stmt->execute()) {
    die("Database error (execute credentials): " . $stmt->error);
}

$cred = $stmt->get_result()->fetch_assoc();
echo "API Credentials: ";
var_dump($cred);

if (!$cred) {
    die("Error: Keine aktiven API Credentials gefunden");
}

// Ab hier normal weitermachen mit JSON Response
header('Content-Type: application/json');

try {
    // Bitget Client initialisieren
    $bitget = new Bitget($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);

    // Order bei Bitget stornieren
    $response = $bitget->cancel_order([
        'symbol' => $order['symbol'],
        'orderId' => $order['bitget_order_id']
    ]);

    // Order Status in der Datenbank aktualisieren
    $stmt = $db->prepare("
        UPDATE orders 
        SET status = 'cancelled', 
            updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Order erfolgreich storniert'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
