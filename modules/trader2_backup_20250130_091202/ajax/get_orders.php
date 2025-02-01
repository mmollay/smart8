<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget.php');

try {
    // Direkt User 1 verwenden ohne Session-Check
    $user_id = 1;
    
    // Debug: User ID ausgeben
    error_log("Using fixed User ID: " . $user_id);

    // API Credentials holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE id = ? 
        AND platform = 'bitget' 
        AND is_active = 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cred = $stmt->get_result()->fetch_assoc();

    if ($cred) {
        // Bitget Client initialisieren
        $bitget = new Bitget($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);
        
        // Aktuelle Positionen von Bitget holen
        $positions = $bitget->get_positions();
        $prices = [];
        
        // Preise aus den Positionen extrahieren
        if (is_array($positions)) {
            foreach ($positions as $pos) {
                if (isset($pos['symbol'], $pos['markPrice'])) {
                    $symbol = str_replace('_UMCBL', '', $pos['symbol']);
                    $prices[$symbol] = floatval($pos['markPrice']);
                }
            }
        }
    }

    // Debug: Alle Orders zählen
    $result = $db->query("SELECT COUNT(*) as count FROM orders");
    $count = $result->fetch_assoc()['count'];
    error_log("Anzahl aller Orders: " . $count);

    // Debug: Orders für den User zählen
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userCount = $stmt->get_result()->fetch_assoc()['count'];
    error_log("Anzahl Orders für User " . $user_id . ": " . $userCount);

    // Orders der letzten 24 Stunden
    $sql = "
        SELECT 
            o.*,
            CASE 
                WHEN o.closing_price IS NOT NULL 
                THEN ((o.closing_price - o.entry_price) / o.entry_price * 100 * 
                    CASE WHEN o.side = 'buy' THEN 1 ELSE -1 END * o.leverage)
                ELSE NULL 
            END as pnl
        FROM orders o
        WHERE o.id = ?
        AND o.created_at >= NOW() - INTERVAL 24 HOUR
        ORDER BY o.id DESC
    ";

    // Debug: SQL ausgeben
    error_log("SQL: " . $sql);
    error_log("Parameter id: " . $user_id);

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $orders = [];
    
    while ($row = $result->fetch_assoc()) {
        // Live PnL berechnen wenn Preis verfügbar
        if ($row['status'] === 'filled' && isset($prices[$row['symbol']])) {
            $currentPrice = $prices[$row['symbol']];
            $row['pnl'] = ($currentPrice - $row['entry_price']) / $row['entry_price'] * 100 * 
                         ($row['side'] === 'buy' ? 1 : -1) * $row['leverage'];
        }
        
        // Debug: Jede Row ausgeben
        error_log("Order gefunden: " . json_encode($row));
        $orders[] = $row;
    }

    // Debug: Anzahl gefundener Orders
    error_log("Anzahl gefundene Orders: " . count($orders));

    // API Credentials für User 1 holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE id = 1 AND platform = 'bitget' AND is_active = 1
    ");
    $stmt->execute();
    $cred = $stmt->get_result()->fetch_assoc();

    if (!$cred) {
        throw new Exception("Keine aktiven API Credentials gefunden");
    }

    // Bitget Client initialisieren
    $bitget = new Bitget($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);
    
    // Aktuelle Positionen von Bitget holen
    $positions = $bitget->get_positions();
    
    $bitget_orders = [];
    
    // Positionen in unser Format umwandeln
    if (is_array($positions)) {
        foreach ($positions as $pos) {
            if (isset($pos['holdSide']) && $pos['total'] > 0) {  // Nur aktive Positionen
                $symbol = str_replace('_UMCBL', '', $pos['symbol']);
                $bitget_orders[] = [
                    'symbol' => $symbol,
                    'side' => strtolower($pos['holdSide']),
                    'position_size' => $pos['total'],
                    'entry_price' => $pos['averageOpenPrice'],
                    'leverage' => $pos['leverage'],
                    'status' => 'filled',
                    'pnl' => $pos['unrealizedPL'],
                    'mark_price' => $pos['markPrice']
                ];
            }
        }
    }
    
    // Offene Orders von Bitget holen
    $pending_orders = $bitget->get_open_orders();
    
    // Offene Orders in unser Format umwandeln
    if (is_array($pending_orders)) {
        foreach ($pending_orders as $order) {
            $symbol = str_replace('_UMCBL', '', $order['symbol']);
            $bitget_orders[] = [
                'symbol' => $symbol,
                'side' => strtolower($order['side']),
                'position_size' => $order['size'],
                'entry_price' => $order['price'],
                'leverage' => $order['leverage'],
                'status' => 'placed',
                'bitget_order_id' => $order['orderId']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'orders' => array_merge($orders, $bitget_orders),
        'debug' => [
            'id' => $user_id,
            'total_orders' => $count,
            'user_orders' => $userCount
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_orders.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
