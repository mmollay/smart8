<?php
require __DIR__ . '/../t_config.php';
require __DIR__ . '/../bitget/bitget.php';

class OrderMonitor {
    private $bitget;
    private $db;
    private $ws;
    private $active_orders = [];

    public function __construct($user_id) {
        // DB Connection ist schon in t_config.php
        $this->db = $GLOBALS['db'];
        
        // API Keys laden
        $stmt = $this->db->prepare("SELECT api_key, api_secret, api_passphrase FROM api_credentials WHERE user_id = ? AND platform = 'bitget' AND is_active = 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $api_data = $result->fetch_assoc();

        if (!$api_data) {
            throw new Exception("Keine API Credentials gefunden");
        }

        // Bitget Client initialisieren
        $this->bitget = new Bitget($api_data['api_key'], $api_data['api_secret'], $api_data['api_passphrase']);
        
        // WebSocket verbinden
        $this->connect_websocket();
    }

    private function connect_websocket() {
        $this->ws = new WebSocket\Client("wss://ws.bitget.com/mix/v1/stream");
        
        // Login
        $timestamp = time() * 1000;
        $sign = base64_encode(hash_hmac('sha256', $timestamp . "GET/user/verify", $this->bitget->get_api_secret(), true));
        
        $login_message = json_encode([
            'op' => 'login',
            'args' => [[
                'apiKey' => $this->bitget->get_api_key(),
                'passphrase' => $this->bitget->get_api_passphrase(),
                'timestamp' => $timestamp,
                'sign' => $sign
            ]]
        ]);
        
        $this->ws->send($login_message);
        
        // Subscribe to private order updates
        $subscribe_message = json_encode([
            'op' => 'subscribe',
            'args' => [
                [
                    'instType' => 'UMCBL',
                    'channel' => 'orders',
                    'instId' => 'default'
                ]
            ]
        ]);
        
        $this->ws->send($subscribe_message);
    }

    public function monitor_order($order_id, $tp_order_id = null, $sl_order_id = null) {
        $this->active_orders[$order_id] = [
            'main_order' => $order_id,
            'tp_order' => $tp_order_id,
            'sl_order' => $sl_order_id
        ];
    }

    public function start() {
        while (true) {
            try {
                $message = $this->ws->receive();
                $data = json_decode($message, true);

                if (isset($data['data'])) {
                    foreach ($data['data'] as $order_update) {
                        $this->handle_order_update($order_update);
                    }
                }
            } catch (Exception $e) {
                error_log("WebSocket Error: " . $e->getMessage());
                // Reconnect bei Verbindungsverlust
                sleep(5);
                $this->connect_websocket();
            }
        }
    }

    private function handle_order_update($order_update) {
        $order_id = $order_update['orderId'];
        
        // Prüfen ob es eine unserer Orders ist
        if (!isset($this->active_orders[$order_id])) {
            return;
        }

        $status = $order_update['state'];
        $order_info = $this->active_orders[$order_id];

        // Order Status in DB updaten
        $this->update_order_status($order_id, $status);

        // Bei filled oder cancelled
        if ($status === 'filled' || $status === 'cancelled') {
            // TP/SL Orders löschen wenn Hauptorder ausgeführt oder cancelled
            if ($order_id === $order_info['main_order']) {
                if ($order_info['tp_order']) {
                    $this->bitget->cancel_order(['orderId' => $order_info['tp_order']]);
                }
                if ($order_info['sl_order']) {
                    $this->bitget->cancel_order(['orderId' => $order_info['sl_order']]);
                }
            }

            // Weitere Aktionen nach dem Verkauf
            if ($status === 'filled' && $order_update['side'] === 'close_long' || $order_update['side'] === 'close_short') {
                $this->handle_position_closed($order_update);
            }

            // Order aus Monitoring entfernen
            unset($this->active_orders[$order_id]);
        }
    }

    private function update_order_status($order_id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE bitget_order_id = ?");
        $stmt->bind_param("ss", $status, $order_id);
        $stmt->execute();
    }

    private function handle_position_closed($order_update) {
        // Hier können weitere Aktionen nach dem Verkauf implementiert werden
        // z.B. E-Mail senden, Statistiken aktualisieren, etc.
        
        // Position in DB als geschlossen markieren
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET 
                closed_at = NOW(),
                closing_price = ?,
                pnl = ?
            WHERE bitget_order_id = ?
        ");
        $stmt->bind_param(
            "dds",
            $order_update['price'],
            $order_update['pnl'],
            $order_update['orderId']
        );
        $stmt->execute();
    }
}

// WebSocket-Monitor für Bitget Orders implementieren
while (true) {
    try {
        // Alle aktiven API Credentials holen
        $stmt = $GLOBALS['db']->prepare("
            SELECT ac.*, u.id as user_id 
            FROM api_credentials ac
            JOIN users u ON u.id = ac.user_id
            WHERE ac.platform = 'bitget' 
            AND ac.is_active = 1
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($cred = $result->fetch_assoc()) {
            try {
                // Bitget Client initialisieren
                $bitget = new Bitget($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);

                // Offene Orders von Bitget abrufen
                $orders = $bitget->get_open_orders();
                if (is_array($orders)) {
                    foreach ($orders as $order) {
                        if (isset($order['orderId'])) {
                            $stmt = $GLOBALS['db']->prepare("
                                UPDATE orders 
                                SET status = ?,
                                    updated_at = NOW()
                                WHERE bitget_order_id = ?
                                AND user_id = ?
                            ");
                            $status = $order['status'];
                            $stmt->bind_param("ssi", $status, $order['orderId'], $cred['user_id']);
                            $stmt->execute();
                        }
                    }
                }

                // Positions von Bitget abrufen
                $positions = $bitget->get_positions();
                if (is_array($positions)) {
                    foreach ($positions as $pos) {
                        if (isset($pos['symbol'])) {
                            // Symbol bereinigen (BTCUSDT_UMCBL -> BTCUSDT)
                            $symbol = str_replace('_UMCBL', '', $pos['symbol']);
                            
                            $stmt = $GLOBALS['db']->prepare("
                                UPDATE orders 
                                SET entry_price = ?,
                                    position_size = ?,
                                    updated_at = NOW()
                                WHERE symbol = ?
                                AND user_id = ?
                                AND status = 'filled'
                            ");
                            $entryPrice = floatval($pos['averageOpenPrice']);
                            $size = floatval($pos['total']);
                            $stmt->bind_param("ddsi", 
                                $entryPrice,
                                $size,
                                $symbol,
                                $cred['user_id']
                            );
                            $stmt->execute();

                            // Aktuelle Marktpreise speichern
                            $stmt = $GLOBALS['db']->prepare("
                                INSERT INTO market_prices (symbol, price) 
                                VALUES (?, ?)
                            ");
                            $markPrice = floatval($pos['markPrice']);
                            $stmt->bind_param("sd", 
                                $symbol,
                                $markPrice
                            );
                            $stmt->execute();
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error processing user " . $cred['user_id'] . ": " . $e->getMessage());
                continue;
            }
        }

    } catch (Exception $e) {
        error_log("Error in order_monitor.php: " . $e->getMessage());
    }

    // Alle 5 Sekunden aktualisieren
    sleep(5);
}
