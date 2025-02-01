<?php
require_once(__DIR__ . '/../config/t_config.php');
require_once(__DIR__ . '/../classes/OrderManager.php');
require_once(__DIR__ . '/../classes/BitgetTrading.php');
require_once(__DIR__ . '/../classes/MarketData.php');

try {
    // Hole offene Orders
    $query = "SELECT o.*, ac.api_key, ac.api_secret, ac.api_passphrase 
             FROM orders o
             JOIN api_credentials ac ON o.user_id = ac.user_id
             WHERE o.status IN ('open', 'partial')
             AND ac.platform = 'bitget'
             AND ac.is_active = 1";
             
    $result = $db->query($query);
    
    if (!$result) {
        throw new Exception("Fehler beim Laden der Orders");
    }
    
    // Gruppiere Orders nach User/API-Key
    $ordersByUser = [];
    while ($row = $result->fetch_assoc()) {
        $key = $row['api_key'];
        if (!isset($ordersByUser[$key])) {
            $ordersByUser[$key] = [
                'credentials' => [
                    'api_key' => $row['api_key'],
                    'api_secret' => $row['api_secret'],
                    'api_passphrase' => $row['api_passphrase']
                ],
                'orders' => []
            ];
        }
        $ordersByUser[$key]['orders'][] = $row;
    }
    
    // Aktualisiere Orders für jeden User
    foreach ($ordersByUser as $userData) {
        try {
            $bitget = new BitgetTrading(
                $userData['credentials']['api_key'],
                $userData['credentials']['api_secret'],
                $userData['credentials']['api_passphrase']
            );
            
            $marketData = new MarketData($db);
            $orderManager = new OrderManager($db, $bitget, $marketData);
            
            foreach ($userData['orders'] as $order) {
                try {
                    $orderManager->updateOrderStatus($order['order_id']);
                    echo "Order {$order['order_id']} aktualisiert\n";
                    
                } catch (Exception $e) {
                    logError("Fehler beim Aktualisieren der Order", [
                        'error' => $e->getMessage(),
                        'orderId' => $order['order_id']
                    ]);
                    
                    echo "Fehler bei Order {$order['order_id']}: " . $e->getMessage() . "\n";
                    continue;
                }
            }
            
        } catch (Exception $e) {
            logError("Fehler bei der Verarbeitung des Users", [
                'error' => $e->getMessage(),
                'api_key' => $userData['credentials']['api_key']
            ]);
            
            echo "Fehler bei API-Key {$userData['credentials']['api_key']}: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
} catch (Exception $e) {
    logError("Kritischer Fehler bei der Order-Aktualisierung", [
        'error' => $e->getMessage()
    ]);
    
    echo "Kritischer Fehler: " . $e->getMessage() . "\n";
    exit(1);
}
