<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../t_config.php');

try {
    if (!isset($_GET['user_id'])) {
        throw new Exception('User ID ist erforderlich');
    }

    $user_id = intval($_GET['user_id']);

    // Hole das default_parameter_model_id des Users
    $stmt = $db->prepare("
        SELECT 
            u.default_parameter_model_id,
            m.id as model_id,
            m.name as model_name,
            m.description
        FROM users u
        LEFT JOIN trading_parameter_models m ON m.id = u.default_parameter_model_id
        WHERE u.id = ? AND u.active = 1
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User nicht gefunden oder nicht aktiv');
    }

    $userData = $result->fetch_assoc();
    
    if (!$userData['model_id']) {
        // Kein Modell zugewiesen, Standard-Antwort
        echo json_encode([
            'success' => true,
            'model_id' => null
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'model_id' => $userData['model_id'],
        'model_name' => $userData['model_name'],
        'description' => $userData['description']
    ]);

} catch (Exception $e) {
    error_log('Error in get_user_model.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
