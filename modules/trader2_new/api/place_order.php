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
    
    // Validiere User
    if (!isset($data['user_id'])) {
        throw new Exception('User ID muss angegeben werden');
    }
    
    // Hole API Credentials
    $query = "SELECT * FROM api_credentials 
              WHERE user_id = ? 
              AND platform = 'bitget' 
              AND is_active = 1";
              
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $data['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Keine aktiven API-Zugangsdaten gefunden');
    }
    
    $credentials = $result->fetch_assoc();
    
    // Initialisiere benötigte Klassen
    $bitget = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    $marketData = new MarketData($db);
    $orderManager = new OrderManager($db, $bitget, $marketData);
    
    // Platziere Order
    $response = $orderManager->placeFutureOrder($data);
    
    // Aktualisiere last_used Timestamp
    $updateQuery = "UPDATE api_credentials 
                   SET last_used = NOW() 
                   WHERE id = ?";
                   
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bind_param('i', $credentials['id']);
    $updateStmt->execute();
    
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
