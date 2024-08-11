<?php
include_once (__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateFileList') {
    $update_id = intval($_POST['update_id']);
    $fileList = json_decode($_POST['fileList'], true);

    if ($update_id && is_array($fileList)) {
        // Löschen Sie zuerst alle bestehenden Einträge für diesen update_id
        $stmt = $db->prepare("DELETE FROM email_attachments WHERE email_content_id = ?");
        $stmt->bind_param("i", $update_id);
        $stmt->execute();

        // Fügen Sie die neuen Einträge hinzu
        $stmt = $db->prepare("INSERT INTO email_attachments (email_content_id, file_name, file_type, file_size) VALUES (?, ?, ?, ?)");
        foreach ($fileList as $file) {
            $stmt->bind_param("issi", $update_id, $file['name'], $file['type'], $file['size']);
            $stmt->execute();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ungültige Daten']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ungültige Anfrage']);
}