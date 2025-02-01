<?php
class PositionTracker {
    private $db;
    private $bitget;
    
    public function __construct($db, $bitget) {
        $this->db = $db;
        $this->bitget = $bitget;
    }
    
    public function trackPositions($userId = null) {
        try {
            // Hole aktive Positionen von BitGet
            $positions = $this->bitget->getPositions();
            
            // Beginne Transaktion
            $this->db->begin_transaction();
            
            // Aktualisiere Position-Tracking
            foreach ($positions as $position) {
                $this->updatePosition($position);
                $this->checkTriggers($position);
            }
            
            // Markiere geschlossene Positionen
            $this->markClosedPositions($positions);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Fehler beim Position-Tracking", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
    
    private function updatePosition($position) {
        // Aktualisiere oder erstelle Position
        $query = "INSERT INTO positions (
                    user_id, symbol, side, size, 
                    entry_price, mark_price, leverage,
                    unrealized_pnl, margin_ratio,
                    liquidation_price, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    mark_price = VALUES(mark_price),
                    unrealized_pnl = VALUES(unrealized_pnl),
                    margin_ratio = VALUES(margin_ratio),
                    liquidation_price = VALUES(liquidation_price),
                    updated_at = NOW()";
                    
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            'issddddddd',
            $position['userId'],
            $position['symbol'],
            $position['side'],
            $position['size'],
            $position['entryPrice'],
            $position['markPrice'],
            $position['leverage'],
            $position['unrealizedPnl'],
            $position['marginRatio'],
            $position['liquidationPrice']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Fehler beim Aktualisieren der Position");
        }
        
        // Aktualisiere Position-Historie
        $this->updatePositionHistory($position);
    }
    
    private function updatePositionHistory($position) {
        $query = "INSERT INTO position_history (
                    position_id, mark_price, unrealized_pnl,
                    margin_ratio, timestamp
                ) VALUES (
                    (SELECT id FROM positions 
                     WHERE user_id = ? AND symbol = ?),
                    ?, ?, ?, NOW()
                )";
                
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            'isddd',
            $position['userId'],
            $position['symbol'],
            $position['markPrice'],
            $position['unrealizedPnl'],
            $position['marginRatio']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Fehler beim Speichern der Position-Historie");
        }
    }
    
    private function checkTriggers($position) {
        // Hole aktive Trigger für die Position
        $query = "SELECT * FROM position_triggers 
                 WHERE user_id = ? 
                 AND symbol = ?
                 AND is_active = 1";
                 
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('is', $position['userId'], $position['symbol']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($trigger = $result->fetch_assoc()) {
            $this->evaluateTrigger($trigger, $position);
        }
    }
    
    private function evaluateTrigger($trigger, $position) {
        $triggered = false;
        $message = '';
        
        switch ($trigger['type']) {
            case 'profit_target':
                $profitPercent = ($position['unrealizedPnl'] / 
                    ($position['size'] * $position['entryPrice'])) * 100;
                    
                if ($profitPercent >= $trigger['value']) {
                    $triggered = true;
                    $message = "Gewinnziel von {$trigger['value']}% erreicht";
                }
                break;
                
            case 'loss_limit':
                $lossPercent = ($position['unrealizedPnl'] / 
                    ($position['size'] * $position['entryPrice'])) * 100;
                    
                if ($lossPercent <= -$trigger['value']) {
                    $triggered = true;
                    $message = "Verluststopp von {$trigger['value']}% erreicht";
                }
                break;
                
            case 'margin_ratio':
                if ($position['marginRatio'] >= $trigger['value']) {
                    $triggered = true;
                    $message = "Margin Ratio von {$trigger['value']}% überschritten";
                }
                break;
                
            case 'price_target':
                if ($position['side'] === 'buy' && 
                    $position['markPrice'] >= $trigger['value']) {
                    $triggered = true;
                    $message = "Preis-Ziel von {$trigger['value']} erreicht";
                } elseif ($position['side'] === 'sell' && 
                         $position['markPrice'] <= $trigger['value']) {
                    $triggered = true;
                    $message = "Preis-Ziel von {$trigger['value']} erreicht";
                }
                break;
        }
        
        if ($triggered) {
            $this->executeTriggerAction($trigger, $position, $message);
        }
    }
    
    private function executeTriggerAction($trigger, $position, $message) {
        // Markiere Trigger als ausgeführt
        $query = "UPDATE position_triggers 
                 SET is_active = 0,
                     triggered_at = NOW(),
                     trigger_price = ?
                 WHERE id = ?";
                 
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('di', $position['markPrice'], $trigger['id']);
        $stmt->execute();
        
        // Führe Aktion aus
        switch ($trigger['action']) {
            case 'notify':
                $this->createNotification(
                    $position['userId'],
                    'position_trigger',
                    $trigger['id'],
                    $message
                );
                break;
                
            case 'close':
                try {
                    // Schließe Position
                    $this->bitget->closePosition($position['symbol']);
                    
                    // Erstelle Benachrichtigung
                    $this->createNotification(
                        $position['userId'],
                        'position_closed',
                        $trigger['id'],
                        "Position automatisch geschlossen: {$message}"
                    );
                    
                } catch (Exception $e) {
                    logError("Fehler beim Schließen der Position", [
                        'error' => $e->getMessage(),
                        'trigger' => $trigger,
                        'position' => $position
                    ]);
                    
                    // Benachrichtige über Fehler
                    $this->createNotification(
                        $position['userId'],
                        'error',
                        $trigger['id'],
                        "Fehler beim Schließen der Position: {$e->getMessage()}"
                    );
                }
                break;
                
            case 'reduce':
                try {
                    // Reduziere Position um den angegebenen Prozentsatz
                    $reduceSize = $position['size'] * ($trigger['reduce_percent'] / 100);
                    $this->bitget->reducePosition(
                        $position['symbol'],
                        $reduceSize
                    );
                    
                    // Erstelle Benachrichtigung
                    $this->createNotification(
                        $position['userId'],
                        'position_reduced',
                        $trigger['id'],
                        "Position um {$trigger['reduce_percent']}% reduziert: {$message}"
                    );
                    
                } catch (Exception $e) {
                    logError("Fehler beim Reduzieren der Position", [
                        'error' => $e->getMessage(),
                        'trigger' => $trigger,
                        'position' => $position
                    ]);
                    
                    // Benachrichtige über Fehler
                    $this->createNotification(
                        $position['userId'],
                        'error',
                        $trigger['id'],
                        "Fehler beim Reduzieren der Position: {$e->getMessage()}"
                    );
                }
                break;
        }
    }
    
    private function markClosedPositions($activePositions) {
        // Erstelle Liste der aktiven Positionen
        $activeSymbols = array_column($activePositions, 'symbol');
        $activeUserIds = array_column($activePositions, 'userId');
        
        if (empty($activeSymbols) || empty($activeUserIds)) {
            return;
        }
        
        // Markiere alle anderen Positionen als geschlossen
        $query = "UPDATE positions 
                 SET status = 'closed',
                     closed_at = NOW()
                 WHERE status = 'open'
                 AND (symbol NOT IN (" . str_repeat('?,', count($activeSymbols) - 1) . "?)
                      OR user_id NOT IN (" . str_repeat('?,', count($activeUserIds) - 1) . "?))";
                      
        $stmt = $this->db->prepare($query);
        
        $params = array_merge($activeSymbols, $activeUserIds);
        $types = str_repeat('s', count($activeSymbols)) . 
                str_repeat('i', count($activeUserIds));
                
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
    
    private function createNotification($userId, $type, $referenceId, $message) {
        $query = "INSERT INTO notifications (
                    user_id, type, reference_id,
                    message, created_at
                ) VALUES (?, ?, ?, ?, NOW())";
                
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('isis', $userId, $type, $referenceId, $message);
        
        if (!$stmt->execute()) {
            throw new Exception("Fehler beim Erstellen der Benachrichtigung");
        }
    }
}
