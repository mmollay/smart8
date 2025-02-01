<?php
require_once(__DIR__ . '/../config/t_config.php');

header('Content-Type: application/json');

try {
    // Parameter validieren
    $symbol = $_GET['symbol'] ?? null;
    $modelId = $_GET['model_id'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    $minConfidence = (int)($_GET['min_confidence'] ?? 0);
    
    // Baue Query
    $query = "SELECT 
                s.*,
                m.name as model_name,
                m.description as model_description
             FROM trading_signals s
             JOIN trading_parameter_models m ON s.model_id = m.id
             WHERE 1=1";
             
    $params = [];
    $types = "";
    
    if ($symbol) {
        $query .= " AND s.symbol = ?";
        $params[] = $symbol;
        $types .= "s";
    }
    
    if ($modelId) {
        $query .= " AND s.model_id = ?";
        $params[] = $modelId;
        $types .= "i";
    }
    
    if ($minConfidence > 0) {
        $query .= " AND s.confidence >= ?";
        $params[] = $minConfidence;
        $types .= "i";
    }
    
    $query .= " ORDER BY s.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    // Führe Query aus
    $stmt = $db->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verarbeite Ergebnisse
    $signals = [];
    while ($row = $result->fetch_assoc()) {
        $signals[] = [
            'id' => $row['id'],
            'model' => [
                'id' => $row['model_id'],
                'name' => $row['model_name'],
                'description' => $row['model_description']
            ],
            'symbol' => $row['symbol'],
            'action' => $row['action'],
            'confidence' => floatval($row['confidence']),
            'reasons' => json_decode($row['reasons'], true),
            'parameters' => json_decode($row['parameters'], true),
            'created_at' => $row['created_at']
        ];
    }
    
    // Hole Performance-Metriken
    if (!empty($signals)) {
        $modelIds = array_unique(array_column($signals, 'model_id'));
        
        $metricsQuery = "SELECT 
                          model_id,
                          COUNT(*) as total_signals,
                          SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_signals,
                          AVG(CASE WHEN success = 1 THEN profit ELSE 0 END) as avg_profit
                        FROM signal_results
                        WHERE model_id IN (" . implode(',', $modelIds) . ")
                        GROUP BY model_id";
                        
        $metricsResult = $db->query($metricsQuery);
        
        $metrics = [];
        while ($row = $metricsResult->fetch_assoc()) {
            $metrics[$row['model_id']] = [
                'total_signals' => (int)$row['total_signals'],
                'success_rate' => $row['total_signals'] > 0 
                    ? ($row['successful_signals'] / $row['total_signals'] * 100)
                    : 0,
                'avg_profit' => floatval($row['avg_profit'])
            ];
        }
        
        // Füge Metriken zu den Signalen hinzu
        foreach ($signals as &$signal) {
            $signal['model']['metrics'] = $metrics[$signal['model']['id']] ?? [
                'total_signals' => 0,
                'success_rate' => 0,
                'avg_profit' => 0
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'signals' => $signals
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
