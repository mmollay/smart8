<?php
include(__DIR__ . '/../../../config.php');
header('Content-Type: application/json');

try {
    // Formulardaten empfangen
    $client_id = isset($_POST['update_id']) ? (int) $_POST['update_id'] : 0;

    // Überprüfen, ob die client_id gültig ist
    if ($client_id === 0) {
        throw new Exception('Ungültige client_id.');
    }

    // Daten escapen
    $first_name = $GLOBALS['mysqli']->real_escape_string($_POST['first_name']);
    $last_name = $GLOBALS['mysqli']->real_escape_string($_POST['last_name']);
    $phone = $GLOBALS['mysqli']->real_escape_string($_POST['phone']);
    $street = $GLOBALS['mysqli']->real_escape_string($_POST['street']);
    $zip = $GLOBALS['mysqli']->real_escape_string($_POST['zip']);
    $country = $GLOBALS['mysqli']->real_escape_string($_POST['country']);
    $company = $GLOBALS['mysqli']->real_escape_string($_POST['company']);

    // SQL-Statement mit Prepared Statement
    $stmt = $GLOBALS['mysqli']->prepare("
        UPDATE user2company 
        SET firstname = ?,
            secondname = ?,
            telefon = ?,
            street = ?,
            zip = ?,
            country = ?,
            company1 = ?
        WHERE user_id = ?
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $GLOBALS['mysqli']->error);
    }

    $stmt->bind_param(
        "sssssssi",
        $first_name,
        $last_name,
        $phone,
        $street,
        $zip,
        $country,
        $company,
        $client_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Daten erfolgreich aktualisiert.'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Nicht die Verbindung schließen, da dies in config.php geschieht