<?php
require_once(__DIR__ . '/../t_config.php');

header('Content-Type: application/json');

// Hole alle aktiven Benutzer mit ihren Trading-Modellen und Parameter-Werten
$query = "SELECT 
            u.id as user_id,
            u.username,
            m.id as model_id,
            m.name as model_name,
            m.description as model_description,
            mv.parameter_name,
            mv.parameter_value
          FROM users u
          JOIN user_trading_models utm ON u.id = utm.user_id
          JOIN trading_parameter_models m ON utm.model_id = m.id
          JOIN trading_parameter_model_values mv ON m.id = mv.model_id
          WHERE u.is_active = 1
          AND m.is_active = 1
          ORDER BY u.id, m.id, mv.parameter_name";

$result = $db->query($query);

if ($result) {
    $users = [];
    $currentUser = null;
    $currentModel = null;
    
    while ($row = $result->fetch_assoc()) {
        $userId = intval($row['user_id']);
        $modelId = intval($row['model_id']);
        
        // Wenn wir einen neuen Benutzer haben
        if (!isset($users[$userId])) {
            $users[$userId] = [
                'id' => $userId,
                'username' => $row['username'],
                'model' => null
            ];
        }
        
        // Wenn wir ein neues Modell für den aktuellen Benutzer haben
        if ($users[$userId]['model'] === null || $users[$userId]['model']['id'] !== $modelId) {
            $users[$userId]['model'] = [
                'id' => $modelId,
                'name' => $row['model_name'],
                'description' => $row['model_description'],
                'parameters' => []
            ];
        }
        
        // Parameter zum aktuellen Modell hinzufügen
        $users[$userId]['model']['parameters'][$row['parameter_name']] = floatval($row['parameter_value']);
    }
    
    // Konvertiere das assoziative Array in ein numerisches
    $users = array_values($users);
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Abrufen der Benutzer und Modelle',
        'debug' => [
            'query_error' => $db->error
        ]
    ]);
}
