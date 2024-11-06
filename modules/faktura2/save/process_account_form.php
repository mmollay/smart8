<?php
include(__DIR__ . '/../f_config.php');

// Überprüfen Sie, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sammeln und bereinigen Sie die Eingabedaten
    $account_number = trim($_POST['account_number']);
    $account_name = trim($_POST['account_name']);
    $account_type = trim($_POST['account_type']);
    $percentage = !empty($_POST['percentage']) ? floatval($_POST['percentage']) : null;
    $description = trim($_POST['description']);
    $account_id = !empty($_POST['account_id']) ? intval($_POST['account_id']) : null;

    // Bestimmen des Modus basierend auf dem Vorhandensein von account_id
    $modus = $account_id ? 'update_account' : 'add_account';

    // Grundlegende Validierung
    if (empty($account_number) || empty($account_name) || empty($account_type)) {
        send_response('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
        exit;
    }

    // Überprüfen Sie, ob die Kontonummer bereits existiert (außer für das aktuelle Konto bei Updates)
    $stmt = $db->prepare("SELECT COUNT(*) FROM accounts WHERE account_number = ? AND account_id != ?");
    $stmt->bind_param("si", $account_number, $account_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        send_response('error', 'Diese Kontonummer existiert bereits.');
        exit;
    }

    // Vorbereiten der SQL-Anweisung basierend auf dem Modus
    if ($modus === 'add_account') {
        $sql = "INSERT INTO accounts (account_number, account_name, account_type, percentage, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssds", $account_number, $account_name, $account_type, $percentage, $description);
    } else {
        $sql = "UPDATE accounts SET account_number = ?, account_name = ?, account_type = ?, percentage = ?, description = ? WHERE account_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssdsi", $account_number, $account_name, $account_type, $percentage, $description, $account_id);
    }

    // Ausführen der SQL-Anweisung
    if ($stmt->execute()) {
        send_response('success', 'Konto erfolgreich ' . ($modus === 'add_account' ? 'hinzugefügt' : 'aktualisiert') . '.');
    } else {
        send_response('error', 'Datenbankfehler: ' . $stmt->error);
    }

    $stmt->close();
} else {
    send_response('error', 'Ungültige Anfragemethode.');
}

function send_response($status, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
}