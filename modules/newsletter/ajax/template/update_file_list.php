<?php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

$update_id = $_POST['update_id'] ?? null;
$fileList = json_decode($_POST['fileList'] ?? '[]', true);

if (!$update_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Newsletter ID fehlt'
    ]);
    exit;
}

try {
    // Lösche alte Einträge
    $stmt = $db->prepare("DELETE FROM newsletter_attachments WHERE newsletter_id = ?");
    $stmt->bind_param('i', $update_id);
    $stmt->execute();

    // Füge neue Einträge hinzu
    if (!empty($fileList)) {
        $stmt = $db->prepare("
            INSERT INTO newsletter_attachments 
            (newsletter_id, file_name, file_size, file_type) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($fileList as $file) {
            $stmt->bind_param(
                'isis',
                $update_id,
                $file['name'],
                $file['size'],
                $file['type']
            );
            $stmt->execute();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Dateiliste erfolgreich aktualisiert'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Aktualisieren der Dateiliste: ' . $e->getMessage()
    ]);
}

$db->close();

