<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');

try {
    if (!isset($_GET['model_id'])) {
        throw new Exception('Model ID ist erforderlich');
    }

    $model_id = intval($_GET['model_id']);

    // Hole das Model und seine Parameter
    $stmt = $db->prepare("
        SELECT 
            m.id,
            m.name as model_name,
            m.description,
            mv.parameter_name,
            mv.parameter_value
        FROM trading_parameter_models m
        LEFT JOIN trading_parameter_model_values mv ON mv.model_id = m.id
        WHERE m.id = ? AND m.is_active = 1
    ");

    $stmt->bind_param("i", $model_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Wenn kein Model gefunden wurde, Standard-Parameter zurückgeben
        echo json_encode([
            'success' => true,
            'parameters' => [
                'model_name' => 'Standard Modell',
                'description' => 'Standard Trading Parameter',
                'take_profit_percent' => 2.0,
                'stop_loss_percent' => 1.0,
                'leverage' => 10,
                'position_size_percent' => 10.0
            ]
        ]);
        exit;
    }

    // Parameter aus der Datenbank in ein Array umwandeln
    $model = null;
    $parameters = [];
    while ($row = $result->fetch_assoc()) {
        if (!$model) {
            $model = [
                'id' => $row['id'],
                'model_name' => $row['model_name'],
                'description' => $row['description']
            ];
        }
        if ($row['parameter_name']) {
            $parameters[$row['parameter_name']] = floatval($row['parameter_value']);
        }
    }

    // Standard-Parameter mit den Modell-Parametern zusammenführen
    $defaultParameters = [
        'take_profit_percent' => 2.0,
        'stop_loss_percent' => 1.0,
        'leverage' => 10,
        'position_size_percent' => 10.0
    ];

    $finalParameters = array_merge(
        $defaultParameters,
        $parameters,
        [
            'model_name' => $model['model_name'],
            'description' => $model['description']
        ]
    );

    echo json_encode([
        'success' => true,
        'parameters' => $finalParameters
    ]);

} catch (Exception $e) {
    error_log('Error in get_model_parameters.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
