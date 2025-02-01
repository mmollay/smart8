<?php
class TradingParameters {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getUserParameters($userId) {
        try {
            $query = "SELECT 
                        u.id as user_id,
                        u.username,
                        m.id as model_id,
                        m.name as model_name,
                        m.description,
                        mv.parameter_name,
                        mv.parameter_value
                    FROM users u
                    JOIN user_trading_models utm ON u.id = utm.user_id
                    JOIN trading_parameter_models m ON utm.model_id = m.id
                    JOIN trading_parameter_model_values mv ON m.id = mv.model_id
                    WHERE u.id = ? AND u.is_active = 1 AND m.is_active = 1";
                    
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $parameters = [
                'user' => null,
                'model' => null,
                'parameters' => []
            ];
            
            while ($row = $result->fetch_assoc()) {
                if (!$parameters['user']) {
                    $parameters['user'] = [
                        'id' => $row['user_id'],
                        'username' => $row['username']
                    ];
                    
                    $parameters['model'] = [
                        'id' => $row['model_id'],
                        'name' => $row['model_name'],
                        'description' => $row['description']
                    ];
                }
                
                $parameters['parameters'][$row['parameter_name']] = $this->convertParameterValue($row['parameter_value']);
            }
            
            return $parameters;
            
        } catch (Exception $e) {
            logError("Fehler beim Laden der Trading-Parameter", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
    
    public function updateModelParameters($modelId, $parameters) {
        try {
            $this->db->begin_transaction();
            
            foreach ($parameters as $name => $value) {
                $query = "INSERT INTO trading_parameter_model_values 
                         (model_id, parameter_name, parameter_value) 
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE parameter_value = ?";
                         
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('isss', $modelId, $name, $value, $value);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Fehler beim Aktualisieren der Modell-Parameter", [
                'error' => $e->getMessage(),
                'model_id' => $modelId,
                'parameters' => $parameters
            ]);
            throw $e;
        }
    }
    
    public function validateParameters($parameters) {
        $required = ['take_profit', 'stop_loss', 'leverage', 'position_size'];
        $missing = [];
        
        foreach ($required as $param) {
            if (!isset($parameters[$param])) {
                $missing[] = $param;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("Fehlende Parameter: " . implode(', ', $missing));
        }
        
        // Validiere Wertebereiche
        if ($parameters['leverage'] < 1 || $parameters['leverage'] > 100) {
            throw new Exception("Ungültiger Hebel (1-100): " . $parameters['leverage']);
        }
        
        if ($parameters['take_profit'] <= 0 || $parameters['stop_loss'] <= 0) {
            throw new Exception("Take-Profit und Stop-Loss müssen größer als 0 sein");
        }
        
        if ($parameters['position_size'] <= 0) {
            throw new Exception("Position Size muss größer als 0 sein");
        }
        
        return true;
    }
    
    private function convertParameterValue($value) {
        // Versuche den Wert als Nummer zu konvertieren
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        // Prüfe auf boolesche Werte
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        
        // Ansonsten behalte den String
        return $value;
    }
    
    public function getDefaultParameters() {
        return [
            'take_profit' => 2.0,   // 2% Take-Profit
            'stop_loss' => 1.0,     // 1% Stop-Loss
            'leverage' => 5,         // 5x Hebel
            'position_size' => 0.1,  // 10% des verfügbaren Kapitals
            'risk_level' => 'moderat'
        ];
    }
}
