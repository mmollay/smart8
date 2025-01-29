<?php
require_once(__DIR__ . '/../t_config.php');

header('Content-Type: application/json');

try {
    $model_id = isset($_POST['model_id']) ? intval($_POST['model_id']) : null;
    
    if (!$model_id) {
        throw new Exception('Keine Modell-ID angegeben');
    }

    // Check if model exists and is active
    $model_check = $db->prepare("SELECT id FROM trading_parameter_models WHERE id = ? AND is_active = 1");
    $model_check->bind_param('i', $model_id);
    $model_check->execute();
    if ($model_check->get_result()->num_rows === 0) {
        throw new Exception('Ungültiges oder inaktives Modell');
    }

    // Get all parameters for this model
    $params_query = $db->prepare("
        SELECT id, parameter_name, parameter_value, 
               CASE 
                   WHEN parameter_name LIKE 'tp_%' THEN 'Take Profit'
                   WHEN parameter_name LIKE 'sl_%' THEN 'Stop Loss'
                   WHEN parameter_name LIKE 'default_%' THEN 'Standard'
                   ELSE 'Sonstige'
               END as parameter_type
        FROM trading_parameter_model_values 
        WHERE model_id = ?
        ORDER BY parameter_type, parameter_name
    ");
    $params_query->bind_param('i', $model_id);
    $params_query->execute();
    $result = $params_query->get_result();

    $parameters = [];
    while ($row = $result->fetch_assoc()) {
        $parameters[] = [
            'id' => $row['id'],
            'parameter_name' => $row['parameter_name'],
            'parameter_value' => floatval($row['parameter_value']),
            'type' => $row['parameter_type']
        ];
    }

    echo json_encode([
        'success' => true,
        'parameters' => $parameters
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
