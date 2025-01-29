<?php
require_once(__DIR__ . '/../t_config.php');

// Logging aktivieren
error_log("BitGet Webhook aufgerufen");

// Debug: Datenbank-Verbindung prüfen
error_log("Database name: " . $db->query("SELECT DATABASE()")->fetch_row()[0]);

// Debug: API Credentials Table prüfen
$tables = $db->query("SHOW TABLES LIKE 'api_credentials'")->num_rows;
error_log("api_credentials table exists: " . ($tables > 0 ? 'yes' : 'no'));

// Debug: Credentials zählen
$count = $db->query("SELECT COUNT(*) FROM api_credentials WHERE platform = 'bitget'")->fetch_row()[0];
error_log("Number of BitGet credentials: " . $count);

// Rohe POST-Daten lesen
$raw_post = file_get_contents('php://input');
error_log("Webhook Rohdaten: " . $raw_post);

try {
    // JSON dekodieren
    $data = json_decode($raw_post, true);
    if (!$data) {
        throw new Exception("Ungültige JSON-Daten");
    }

    // Validiere Webhook-Signatur
    $headers = getallheaders();
    $signature = isset($headers['X-Bit-Sign']) ? $headers['X-Bit-Sign'] : '';
    
    // API Credentials holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE platform = 'bitget' 
        AND is_active = 1 
        ORDER BY last_used DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $cred = $stmt->get_result()->fetch_assoc();

    if (!$cred) {
        // Debug: SQL für Fehlersuche
        error_log("SQL Debug - All Credentials:");
        $result = $db->query("SELECT id, platform, is_active, created_at FROM api_credentials");
        while ($row = $result->fetch_assoc()) {
            error_log(print_r($row, true));
        }
        throw new Exception("Keine API Credentials gefunden");
    }

    // Debug: Gefundene Credentials
    error_log("Found credentials - ID: " . $cred['id'] . ", Platform: " . $cred['platform']);

    // Debug: Signatur-Überprüfung
    error_log("Received signature: " . $signature);
    $expected_signature = base64_encode(hash_hmac('sha256', '', $cred['api_secret'], true));
    error_log("Expected signature: " . $expected_signature);
    
    if ($signature !== $expected_signature) {
        throw new Exception("Ungültige Signatur");
    }

    // Order-Update verarbeiten
    if (isset($data['data'])) {
        foreach ($data['data'] as $update) {
            $symbol = str_replace('_UMCBL', '', $update['symbol']);
            $bitget_order_id = $update['orderId'];
            
            // Status-Mapping
            $status = 'unknown';
            switch ($update['status']) {
                case 'new':
                    $status = 'placed';
                    break;
                case 'filled':
                    $status = 'filled';
                    break;
                case 'canceled':
                    $status = 'cancelled';
                    break;
                case 'partial-filled':
                    $status = 'partially_filled';
                    break;
            }

            // Debug: Order-Daten
            error_log("Verarbeite Order:");
            error_log("Symbol: " . $symbol);
            error_log("OrderID: " . $bitget_order_id);
            error_log("Status: " . $status);

            // Zuerst prüfen ob die Order existiert
            $check_stmt = $db->prepare("SELECT id FROM orders WHERE bitget_order_id = ?");
            $check_stmt->bind_param("s", $bitget_order_id);
            $check_stmt->execute();
            $existing_order = $check_stmt->get_result()->fetch_assoc();

            if ($existing_order) {
                // Order aktualisieren
                $update_stmt = $db->prepare("
                    UPDATE orders 
                    SET status = ?,
                        updated_at = NOW(),
                        closing_price = CASE 
                            WHEN ? IN ('filled', 'cancelled') THEN ?
                            ELSE closing_price 
                        END,
                        closed_at = CASE 
                            WHEN ? IN ('filled', 'cancelled') THEN NOW()
                            ELSE closed_at 
                        END
                    WHERE id = ?
                ");
                
                $closing_price = isset($update['priceAvg']) ? $update['priceAvg'] : null;
                $update_stmt->bind_param("ssdsi", 
                    $status, 
                    $status, 
                    $closing_price, 
                    $status, 
                    $existing_order['id']
                );
                
                if ($update_stmt->execute()) {
                    error_log("Order {$existing_order['id']} aktualisiert: Status = {$status}");
                } else {
                    error_log("Fehler beim Aktualisieren der Order {$existing_order['id']}: " . $update_stmt->error);
                }
            } else {
                // Neue Order erstellen
                $insert_stmt = $db->prepare("
                    INSERT INTO orders (
                        user_id, parameter_model_id, symbol, side, 
                        position_size, entry_price, leverage, status, 
                        bitget_order_id, created_at
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, NOW()
                    )
                ");
                
                // Debug: Parameter
                $user_id = $cred['user_id'];
                $parameter_model_id = 1;
                $side = strtolower($update['side']);
                $size = floatval($update['size']);
                $price = isset($update['priceAvg']) ? floatval($update['priceAvg']) : floatval($update['price']);
                $leverage = intval($update['leverage']);

                error_log("Insert Parameter:");
                error_log(sprintf(
                    "user_id: %d, model_id: %d, symbol: %s, side: %s, size: %f, price: %f, leverage: %d, status: %s, order_id: %s",
                    $user_id, $parameter_model_id, $symbol, $side, $size, $price, $leverage, $status, $bitget_order_id
                ));
                
                $insert_stmt->bind_param("iissddiss", 
                    $user_id,           // i
                    $parameter_model_id, // i
                    $symbol,            // s
                    $side,              // s
                    $size,              // d
                    $price,             // d
                    $leverage,          // i
                    $status,            // s
                    $bitget_order_id    // s
                );
                
                if ($insert_stmt->execute()) {
                    error_log("Neue Order erstellt: {$bitget_order_id}");
                } else {
                    error_log("Fehler beim Erstellen der Order {$bitget_order_id}: " . $insert_stmt->error);
                }
            }
        }
    }

    // Erfolg zurückmelden
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Webhook verarbeitet'
    ]);

} catch (Exception $e) {
    error_log("Webhook Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
