<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/OrderManager.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');
require_once(__DIR__ . '/../classes/MarketData.php');

header('Content-Type: application/json');

try {
    // Validiere Request-Methode
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST-Anfragen erlaubt');
    }
    
    // Hole POST-Daten
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Ungültige JSON-Daten');
    }
    
    // Validiere Parameter
    if (!isset($data['order_id']) || !isset($data['user_id'])) {
        throw new Exception('Order ID und User ID müssen angegeben werden');
    }
    
    // Prüfe ob Order zum User gehört
    $query = "SELECT 1 FROM orders 
              WHERE order_id = ? 
              AND user_id = ?";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param('si', $data['order_id'], $data['user_id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Order nicht gefunden oder keine Berechtigung');
    }
    
    // Hole API Credentials
    $credQuery = "SELECT * FROM api_credentials 
                 WHERE user_id = ? 
                 AND platform = 'bitget' 
                 AND is_active = 1";
                 
    $credStmt = $db->prepare($credQuery);
    $credStmt->bind_param('i', $data['user_id']);
    $credStmt->execute();
    $credResult = $credStmt->get_result();
    
    if ($credResult->num_rows === 0) {
        throw new Exception('Keine aktiven API-Zugangsdaten gefunden');
    }
    
    $credentials = $credResult->fetch_assoc();
    
    // Initialisiere benötigte Klassen
    $bitget = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    $marketData = new MarketData($db);
    $orderManager = new OrderManager($db, $bitget, $marketData);
    
    // Storniere Order
    $response = $orderManager->cancelOrder($data['order_id']);
    
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
