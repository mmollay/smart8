<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');

header('Content-Type: application/json');

try {
    // Hole API Credentials
    $query = "SELECT * FROM api_credentials 
              WHERE platform = 'bitget' 
              AND is_active = 1 
              ORDER BY last_used DESC 
              LIMIT 1";
              
    $result = $db->query($query);
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception('Keine aktiven API-Zugangsdaten gefunden');
    }
    
    $credentials = $result->fetch_assoc();
    
    // Initialisiere BitGet API
    $bitget = new BitgetTrading(
        $credentials['api_key'],
        $credentials['api_secret'],
        $credentials['api_passphrase']
    );
    
    // Hole Positionen
    $positions = $bitget->getPositions();
    
    // Hole zugehörige Take-Profit und Stop-Loss Orders
    $enrichedPositions = [];
    foreach ($positions as $position) {
        // Hole TP/SL aus der Datenbank
        $query = "SELECT o.order_id, ro.type, o.price
                 FROM orders o
                 JOIN related_orders ro ON o.order_id = ro.related_order_id
                 WHERE o.symbol = ?
                 AND o.status = 'open'
                 AND ro.type IN ('take_profit', 'stop_loss')";
                 
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $position['symbol']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tp = null;
        $sl = null;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] === 'take_profit') {
                $tp = $row['price'];
            } else if ($row['type'] === 'stop_loss') {
                $sl = $row['price'];
            }
        }
        
        $position['take_profit'] = $tp;
        $position['stop_loss'] = $sl;
        $enrichedPositions[] = $position;
    }
    
    echo json_encode([
        'success' => true,
        'positions' => $enrichedPositions
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
