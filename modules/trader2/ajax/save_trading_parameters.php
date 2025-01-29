<?php
require_once(__DIR__ . '/../t_config.php');

header('Content-Type: application/json');

try {
    // Daten validieren
    $required_fields = ['name'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Feld {$field} ist erforderlich");
        }
    }

    // Basis-Daten
    $name = $_POST['name'];
    $update_id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;
    $leverage = isset($_POST['leverage']) ? intval($_POST['leverage']) : 5;
    $position_size = isset($_POST['position_size']) ? floatval($_POST['position_size']) : 0.01;

    $db->begin_transaction();

    try {
        if ($update_id) {
            // Update
            $update_query = "UPDATE trading_parameter_models SET name = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->prepare($update_query);
            $stmt->bind_param('si', $name, $update_id);
            $stmt->execute();
            $model_id = $update_id;
        } else {
            // Insert
            $insert_query = "INSERT INTO trading_parameter_models (name, is_active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $model_id = $db->insert_id;
        }

        // Alte Parameter löschen
        if ($update_id) {
            $delete_params = "DELETE FROM trading_parameter_model_values WHERE model_id = ?";
            $stmt = $db->prepare($delete_params);
            $stmt->bind_param('i', $model_id);
            $stmt->execute();
        }

        // Standard Parameter speichern
        $standard_params = [
            'leverage' => $leverage,
            'position_size' => $position_size
        ];

        $insert_param = $db->prepare("INSERT INTO trading_parameter_model_values (model_id, parameter_name, parameter_value) VALUES (?, ?, ?)");
        
        foreach ($standard_params as $name => $value) {
            $insert_param->bind_param('isd', $model_id, $name, $value);
            $insert_param->execute();
        }

        // TP/SL Parameter speichern wenn vorhanden
        $percentage_params = [
            'tp_percentage_long', 'tp_percentage_short',
            'sl_percentage_long', 'sl_percentage_short'
        ];

        foreach ($percentage_params as $param) {
            if (isset($_POST[$param])) {
                $value = floatval($_POST[$param]);
                $insert_param->bind_param('isd', $model_id, $param, $value);
                $insert_param->execute();
            }
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => $update_id ? 'Modell erfolgreich aktualisiert' : 'Modell erfolgreich erstellt',
            'model_id' => $model_id
        ]);

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}