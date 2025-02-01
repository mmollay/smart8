<?php
include __DIR__ . '/../../t_config.php';

$response = ['success' => false, 'message' => ''];

try {
    // Daten validieren
    $required_fields = ['user_id', 'parameter_model_id', 'symbol', 'side', 'position_size', 'entry_price'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Feld {$field} ist erforderlich");
        }
    }

    // Order in DB speichern
    $sql = "INSERT INTO orders (
                user_id, 
                parameter_model_id,
                symbol, 
                side, 
                position_size, 
                entry_price, 
                take_profit, 
                stop_loss,
                leverage,
                status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending'
            )";

    $stmt = $db->prepare($sql);
    $stmt->bind_param(
        "iissddddi",
        $_POST['user_id'],
        $_POST['parameter_model_id'],
        $_POST['symbol'],
        $_POST['side'],
        $_POST['position_size'],
        $_POST['entry_price'],
        $_POST['take_profit'],
        $_POST['stop_loss'],
        $_POST['leverage']
    );

    if ($stmt->execute()) {
        $order_id = $db->insert_id;
        
        // Erfolgreiche Antwort
        $response = [
            'success' => true,
            'message' => 'Trade wurde erfolgreich platziert',
            'order_id' => $order_id
        ];
    } else {
        throw new Exception("Fehler beim Speichern des Trades");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
