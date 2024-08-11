<?php
require_once (__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_content_id = intval($_POST['email_content_id']);
    $file_list = json_decode($_POST['file_list'], true);
    $upload_dir = $_POST['upload_dir'];

    // Transaktion starten
    $db->begin_transaction();

    try {
        // Alle bestehenden Einträge für diesen email_content_id löschen
        $stmt = $db->prepare("DELETE FROM email_attachments WHERE email_content_id = ?");
        $stmt->bind_param("i", $email_content_id);
        $stmt->execute();

        // Neue Einträge einfügen
        $stmt = $db->prepare("INSERT INTO email_attachments (email_content_id, file_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?)");

        foreach ($file_list as $file) {
            $file_path = $upload_dir . $file['name'];
            $stmt->bind_param("issss", $email_content_id, $file['name'], $file_path, $file['size'], $file['type']);
            $stmt->execute();
        }

        // Transaktion abschließen
        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'File list updated successfully']);
    } catch (Exception $e) {
        // Bei Fehler Transaktion rückgängig machen
        $db->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error updating file list: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}