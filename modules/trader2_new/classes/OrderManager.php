<?php
class OrderManager {
    private $db;
    private $bitget;
    private $marketData;
    
    public function __construct($db, $bitget, $marketData) {
        $this->db = $db;
        $this->bitget = $bitget;
        $this->marketData = $marketData;
    }
    
    public function placeFutureOrder($params) {
        try {
            $this->db->begin_transaction();
            
            // Validiere Parameter
            $requiredParams = ['user_id', 'symbol', 'side', 'size', 'leverage'];
            foreach ($requiredParams as $param) {
                if (!isset($params[$param])) {
                    throw new Exception("Fehlender Parameter: {$param}");
                }
            }
            
            // Hole aktuelle Marktdaten
            $marketData = $this->marketData->getLatestData($params['symbol']);
            if (!$marketData) {
                throw new Exception("Keine Marktdaten verfügbar für: {$params['symbol']}");
            }
            
            // Setze Hebel
            $this->bitget->setLeverage([
                'symbol' => $params['symbol'],
                'leverage' => $params['leverage']
            ]);
            
            // Berechne Take-Profit und Stop-Loss
            $entryPrice = $marketData['price'];
            $takeProfitPrice = $this->calculateTakeProfit($entryPrice, $params);
            $stopLossPrice = $this->calculateStopLoss($entryPrice, $params);
            
            // Platziere Hauptorder
            $mainOrder = $this->bitget->placeOrder([
                'symbol' => $params['symbol'],
                'side' => $params['side'],
                'size' => $params['size'],
                'type' => 'market',
                'leverage' => $params['leverage']
            ]);
            
            if (!isset($mainOrder['orderId'])) {
                throw new Exception("Fehler beim Platzieren der Hauptorder");
            }
            
            // Speichere Order in der Datenbank
            $query = "INSERT INTO orders (
                        user_id, symbol, order_id, side,
                        size, entry_price, take_profit,
                        stop_loss, leverage, status,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
            $stmt = $this->db->prepare($query);
            $status = 'open';
            
            $stmt->bind_param(
                'isssddddis',
                $params['user_id'],
                $params['symbol'],
                $mainOrder['orderId'],
                $params['side'],
                $params['size'],
                $entryPrice,
                $takeProfitPrice,
                $stopLossPrice,
                $params['leverage'],
                $status
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Fehler beim Speichern der Order");
            }
            
            // Platziere Take-Profit Order
            if ($takeProfitPrice) {
                $tpOrder = $this->bitget->placeOrder([
                    'symbol' => $params['symbol'],
                    'side' => $params['side'] === 'buy' ? 'sell' : 'buy',
                    'size' => $params['size'],
                    'type' => 'limit',
                    'price' => $takeProfitPrice,
                    'reduceOnly' => true
                ]);
                
                // Speichere TP-Order
                $this->saveRelatedOrder($mainOrder['orderId'], $tpOrder['orderId'], 'take_profit');
            }
            
            // Platziere Stop-Loss Order
            if ($stopLossPrice) {
                $slOrder = $this->bitget->placeOrder([
                    'symbol' => $params['symbol'],
                    'side' => $params['side'] === 'buy' ? 'sell' : 'buy',
                    'size' => $params['size'],
                    'type' => 'stop',
                    'stopPrice' => $stopLossPrice,
                    'reduceOnly' => true
                ]);
                
                // Speichere SL-Order
                $this->saveRelatedOrder($mainOrder['orderId'], $slOrder['orderId'], 'stop_loss');
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'orderId' => $mainOrder['orderId'],
                'entryPrice' => $entryPrice,
                'takeProfit' => $takeProfitPrice,
                'stopLoss' => $stopLossPrice
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Fehler beim Platzieren der Order", [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw $e;
        }
    }
    
    private function saveRelatedOrder($mainOrderId, $relatedOrderId, $type) {
        $query = "INSERT INTO related_orders (
                    main_order_id, related_order_id,
                    type, created_at
                ) VALUES (?, ?, ?, NOW())";
                
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('sss', $mainOrderId, $relatedOrderId, $type);
        
        if (!$stmt->execute()) {
            throw new Exception("Fehler beim Speichern der {$type}-Order");
        }
    }
    
    private function calculateTakeProfit($entryPrice, $params) {
        if (!isset($params['take_profit_percent'])) {
            return null;
        }
        
        $multiplier = $params['side'] === 'buy' ? (1 + $params['take_profit_percent'] / 100)
                                               : (1 - $params['take_profit_percent'] / 100);
                                               
        return round($entryPrice * $multiplier, 8);
    }
    
    private function calculateStopLoss($entryPrice, $params) {
        if (!isset($params['stop_loss_percent'])) {
            return null;
        }
        
        $multiplier = $params['side'] === 'buy' ? (1 - $params['stop_loss_percent'] / 100)
                                               : (1 + $params['stop_loss_percent'] / 100);
                                               
        return round($entryPrice * $multiplier, 8);
    }
    
    public function updateOrderStatus($orderId) {
        try {
            // Hole Order-Status von BitGet
            $orderInfo = $this->bitget->getOrder(['orderId' => $orderId]);
            
            if (!$orderInfo) {
                throw new Exception("Order nicht gefunden: {$orderId}");
            }
            
            // Update Status in der Datenbank
            $query = "UPDATE orders 
                     SET status = ?, 
                         filled_price = ?,
                         updated_at = NOW()
                     WHERE order_id = ?";
                     
            $stmt = $this->db->prepare($query);
            $status = $this->mapOrderStatus($orderInfo['status']);
            $filledPrice = $orderInfo['avgPrice'] ?? null;
            
            $stmt->bind_param('sds', $status, $filledPrice, $orderId);
            
            if (!$stmt->execute()) {
                throw new Exception("Fehler beim Aktualisieren des Order-Status");
            }
            
            return [
                'success' => true,
                'status' => $status,
                'filledPrice' => $filledPrice
            ];
            
        } catch (Exception $e) {
            logError("Fehler beim Aktualisieren des Order-Status", [
                'error' => $e->getMessage(),
                'orderId' => $orderId
            ]);
            throw $e;
        }
    }
    
    private function mapOrderStatus($bitgetStatus) {
        $statusMap = [
            'new' => 'open',
            'filled' => 'filled',
            'canceled' => 'cancelled',
            'rejected' => 'rejected',
            'partially_filled' => 'partial'
        ];
        
        return $statusMap[$bitgetStatus] ?? 'unknown';
    }
    
    public function cancelOrder($orderId) {
        try {
            // Hole zugehörige Orders
            $query = "SELECT related_order_id, type 
                     FROM related_orders 
                     WHERE main_order_id = ?";
                     
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Storniere Hauptorder
            $this->bitget->cancelOrder(['orderId' => $orderId]);
            
            // Storniere zugehörige Orders
            while ($row = $result->fetch_assoc()) {
                try {
                    $this->bitget->cancelOrder(['orderId' => $row['related_order_id']]);
                } catch (Exception $e) {
                    logError("Fehler beim Stornieren der zugehörigen Order", [
                        'error' => $e->getMessage(),
                        'orderId' => $row['related_order_id'],
                        'type' => $row['type']
                    ]);
                }
            }
            
            // Update Status in der Datenbank
            $updateQuery = "UPDATE orders 
                          SET status = 'cancelled',
                              updated_at = NOW()
                          WHERE order_id = ?";
                          
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bind_param('s', $orderId);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Fehler beim Aktualisieren des Order-Status");
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            logError("Fehler beim Stornieren der Order", [
                'error' => $e->getMessage(),
                'orderId' => $orderId
            ]);
            throw $e;
        }
    }
}
