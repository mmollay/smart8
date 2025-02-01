<?php
require_once(__DIR__ . '/../t_config.php');

try {
    $db->begin_transaction();

    $list_id = sanitizeInput($_POST['list_id'] ?? '');
    $id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;
    $operation = $id ? 'UPDATE' : 'INSERT';

    // Prüfung des Datensatzes
    if ($id) {
        $check_sql = "SELECT id, email FROM users WHERE id = ?";
        $stmt = $db->prepare($check_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Datensatz nicht gefunden");
        }
        $userData = $result->fetch_assoc();
        $original_email = $userData['email'];
        $stmt->close();
    }

    // E-Mail Validierung
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Ungültige E-Mail-Adresse");
    }

    // Duplikatsprüfung 
    if ($operation === 'INSERT' || ($operation === 'UPDATE' && $email !== $original_email)) {
        $check = $db->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != COALESCE(?, 0)");
        $username = sanitizeInput($_POST['username'] ?? '');
        $check->bind_param("ssi", $email, $username, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("E-Mail oder Benutzername bereits vergeben");
        }
        $check->close();
    }

    // Benutzerdaten vorbereiten
    $userData = [
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'email' => $email,
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'company' => sanitizeInput($_POST['company'] ?? ''),  // Leerer String als Default
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'address_street' => sanitizeInput($_POST['address_street'] ?? ''),
        'address_number' => sanitizeInput($_POST['address_number'] ?? ''),
        'address_zip' => sanitizeInput($_POST['address_zip'] ?? ''),
        'address_city' => sanitizeInput($_POST['address_city'] ?? ''),
        'address_country' => sanitizeInput($_POST['address_country'] ?? ''),
        'active' => isset($_POST['active']) ? 1 : 0,
        'default_parameter_model_id' => !empty($_POST['default_parameter_model_id']) ? intval($_POST['default_parameter_model_id']) : null
    ];

    // Passwort
    if ($operation === 'INSERT' || !empty($_POST['password'])) {
        $userData['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // User speichern
    if ($operation === 'INSERT') {
        $columns = implode(", ", array_keys($userData));
        $placeholders = str_repeat("?,", count($userData) - 1) . "?";
        $sql = "INSERT INTO users ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $types = str_repeat("s", count($userData) - 1) . "i"; // Letzter Parameter ist default_parameter_model_id (integer)
        $stmt->bind_param($types, ...array_values($userData));
        $stmt->execute();
        $userId = $stmt->insert_id;
    } else {
        $setClause = implode(" = ?, ", array_keys($userData)) . " = ?";
        $sql = "UPDATE users SET $setClause WHERE id = ?";
        $stmt = $db->prepare($sql);
        $values = array_values($userData);
        $values[] = $id;
        $types = str_repeat("s", count($userData) - 1) . "ii"; // Letzter Parameter ist default_parameter_model_id (integer) + id
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $userId = $id;
    }

    // API Daten speichern
    if (!empty($_POST['api_key']) || !empty($_POST['api_secret']) || !empty($_POST['api_passphrase'])) {
        $stmt = $db->prepare("DELETE FROM api_credentials WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $apiData = [
            'user_id' => $userId,
            'platform' => sanitizeInput($_POST['platform'] ?? 'bitget'),
            'api_key' => sanitizeInput($_POST['api_key'] ?? ''),
            'api_secret' => sanitizeInput($_POST['api_secret'] ?? ''),
            'api_passphrase' => sanitizeInput($_POST['api_passphrase'] ?? ''),
            'is_active' => 1
        ];

        $columns = implode(", ", array_keys($apiData));
        $placeholders = str_repeat("?,", count($apiData) - 1) . "?";
        $sql = "INSERT INTO api_credentials ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("issssi", ...array_values($apiData));
        $stmt->execute();
    }

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Fehler in form_save.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Fehler: " . $e->getMessage()
    ]);
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}