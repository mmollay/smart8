<?php
require_once __DIR__ . '/mysql.php';

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Daten aus dem Formular holen
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Überprüfen, ob alle erforderlichen Felder ausgefüllt sind
    if (empty($first_name) || empty($last_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Bitte füllen Sie alle Pflichtfelder aus.']);
        exit;
    }

    // E-Mail-Adresse validieren
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.']);
        exit;
    }

    // SQL-Abfrage vorbereiten
    if ($id > 0) {
        // Update bestehender Datensatz
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssi", $first_name, $last_name, $email, $id);
    } else {
        // Neuen Datensatz einfügen
        $sql = "INSERT INTO users (first_name, last_name, email) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sss", $first_name, $last_name, $email);
    }

    // Abfrage ausführen
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Daten erfolgreich gespeichert.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern der Daten: ' . $db->error]);
    }

    // Statement schließen
    $stmt->close();
} else {
    // Wenn keine POST-Anfrage, Fehlermeldung zurückgeben
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage-Methode.']);
}

// Datenbankverbindung schließen
$db->close();
