<?php
class SignalGenerator {
    private $db;
    private $marketData;
    
    public function __construct($db, $marketData) {
        $this->db = $db;
        $this->marketData = $marketData;
    }
    
    public function generateSignals($symbol) {
        try {
            // Hole die neuesten Marktdaten
            $data = $this->marketData->getHistoricalData($symbol, '15m', 100);
            if (empty($data)) {
                throw new Exception("Keine Marktdaten verfügbar für {$symbol}");
            }
            
            // Hole Trading-Parameter für alle aktiven Modelle
            $models = $this->getActiveModels();
            $signals = [];
            
            foreach ($models as $model) {
                $signal = $this->analyzeMarket($data, $model);
                if ($signal) {
                    $signals[] = $this->saveSignal($symbol, $signal, $model);
                }
            }
            
            return $signals;
            
        } catch (Exception $e) {
            logError("Fehler bei der Signalgenerierung", [
                'error' => $e->getMessage(),
                'symbol' => $symbol
            ]);
            throw $e;
        }
    }
    
    private function getActiveModels() {
        $query = "SELECT 
                    m.*,
                    GROUP_CONCAT(
                        CONCAT(mv.parameter_name, ':', mv.parameter_value)
                    ) as parameters
                 FROM trading_parameter_models m
                 LEFT JOIN trading_parameter_model_values mv 
                    ON m.id = mv.model_id
                 WHERE m.is_active = 1
                 GROUP BY m.id";
                 
        $result = $this->db->query($query);
        
        if (!$result) {
            throw new Exception("Fehler beim Laden der Trading-Modelle");
        }
        
        $models = [];
        while ($row = $result->fetch_assoc()) {
            $parameters = [];
            if ($row['parameters']) {
                foreach (explode(',', $row['parameters']) as $param) {
                    list($key, $value) = explode(':', $param);
                    $parameters[$key] = $this->convertParameterValue($value);
                }
            }
            
            $row['parameters'] = $parameters;
            $models[] = $row;
        }
        
        return $models;
    }
    
    private function analyzeMarket($data, $model) {
        // Extrahiere die letzten Preise und Indikatoren
        $prices = array_column($data, 'close');
        $volumes = array_column($data, 'volume');
        $rsi = array_column($data, 'rsi');
        $ema20 = array_column($data, 'ema20');
        $ema50 = array_column($data, 'ema50');
        
        // Aktuelle Werte
        $currentPrice = $prices[0];
        $currentRsi = $rsi[0];
        $currentEma20 = $ema20[0];
        $currentEma50 = $ema50[0];
        
        // Analysiere basierend auf Modell-Parametern
        $signal = null;
        $confidence = 0;
        $reasons = [];
        
        // RSI-Signale
        if ($currentRsi <= ($model['parameters']['rsi_oversold'] ?? 30)) {
            $signal = 'buy';
            $confidence += 30;
            $reasons[] = "RSI überkauft ({$currentRsi})";
        } elseif ($currentRsi >= ($model['parameters']['rsi_overbought'] ?? 70)) {
            $signal = 'sell';
            $confidence += 30;
            $reasons[] = "RSI überverkauft ({$currentRsi})";
        }
        
        // Trend-Signale (EMA-Kreuzungen)
        if ($currentEma20 > $currentEma50 && $ema20[1] <= $ema50[1]) {
            if ($signal !== 'sell') {
                $signal = 'buy';
                $confidence += 40;
                $reasons[] = "EMA20 kreuzt EMA50 nach oben";
            }
        } elseif ($currentEma20 < $currentEma50 && $ema20[1] >= $ema50[1]) {
            if ($signal !== 'buy') {
                $signal = 'sell';
                $confidence += 40;
                $reasons[] = "EMA20 kreuzt EMA50 nach unten";
            }
        }
        
        // Volumen-Bestätigung
        $avgVolume = array_sum(array_slice($volumes, 0, 20)) / 20;
        if ($volumes[0] > $avgVolume * 1.5) {
            $confidence += 20;
            $reasons[] = "Erhöhtes Handelsvolumen";
        }
        
        // Modell-spezifische Anpassungen
        switch ($model['parameters']['risk_level'] ?? 'moderat') {
            case 'konservativ':
                $confidence *= 0.8;
                $minConfidence = 70;
                break;
                
            case 'aggressiv':
                $confidence *= 1.2;
                $minConfidence = 50;
                break;
                
            default: // moderat
                $minConfidence = 60;
        }
        
        // Prüfe ob Signal stark genug ist
        if ($signal && $confidence >= $minConfidence) {
            return [
                'action' => $signal,
                'confidence' => min(100, $confidence),
                'reasons' => $reasons,
                'parameters' => [
                    'leverage' => $model['parameters']['leverage'] ?? 5,
                    'position_size' => $model['parameters']['position_size'] ?? 0.1,
                    'take_profit_percent' => $model['parameters']['take_profit'] ?? 2,
                    'stop_loss_percent' => $model['parameters']['stop_loss'] ?? 1
                ]
            ];
        }
        
        return null;
    }
    
    private function saveSignal($symbol, $signal, $model) {
        try {
            $this->db->begin_transaction();
            
            // Speichere Signal
            $query = "INSERT INTO trading_signals (
                        model_id, symbol, action,
                        confidence, reasons, parameters,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    
            $stmt = $this->db->prepare($query);
            $reasons = json_encode($signal['reasons']);
            $parameters = json_encode($signal['parameters']);
            
            $stmt->bind_param(
                'issdss',
                $model['id'],
                $symbol,
                $signal['action'],
                $signal['confidence'],
                $reasons,
                $parameters
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Fehler beim Speichern des Signals");
            }
            
            $signalId = $this->db->insert_id;
            
            // Hole zugeordnete User für das Modell
            $userQuery = "SELECT user_id 
                         FROM user_trading_models 
                         WHERE model_id = ?";
                         
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bind_param('i', $model['id']);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            
            // Erstelle Benachrichtigungen für User
            while ($user = $userResult->fetch_assoc()) {
                $notifyQuery = "INSERT INTO notifications (
                                user_id, type, reference_id,
                                message, created_at
                              ) VALUES (?, 'signal', ?,
                                ?, NOW())";
                                
                $notifyStmt = $this->db->prepare($notifyQuery);
                $message = "Neues {$signal['action']}-Signal für {$symbol} " .
                          "mit {$signal['confidence']}% Konfidenz";
                          
                $notifyStmt->bind_param(
                    'iis',
                    $user['user_id'],
                    $signalId,
                    $message
                );
                
                $notifyStmt->execute();
            }
            
            $this->db->commit();
            
            return [
                'id' => $signalId,
                'model_id' => $model['id'],
                'model_name' => $model['name'],
                'symbol' => $symbol,
                'action' => $signal['action'],
                'confidence' => $signal['confidence'],
                'reasons' => $signal['reasons'],
                'parameters' => $signal['parameters']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function convertParameterValue($value) {
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? 
                (float)$value : (int)$value;
        }
        
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        
        return $value;
    }
}
