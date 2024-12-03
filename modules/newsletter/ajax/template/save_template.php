<?php
include(__DIR__ . '/../../n_config.php');
header('Content-Type: application/json');

$update_id = $_POST['update_id'] ?? null;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$html_content = $_POST['html_content'] ?? '';
$subject = $_POST['subject'] ?? '';

if (empty($name) || empty($html_content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Name und Inhalt sind erforderlich'
    ]);
    exit;
}

try {
    if ($update_id) {
        // Prüfe ob das Template dem User gehört
        $checkStmt = $db->prepare("SELECT id FROM email_templates WHERE id = ? AND user_id = ?");
        $checkStmt->bind_param('ii', $update_id, $userId);
        $checkStmt->execute();
        if (!$checkStmt->get_result()->num_rows) {
            throw new Exception('Keine Berechtigung zum Bearbeiten dieses Templates');
        }

        // Update existierendes Template
        $stmt = $db->prepare("
            UPDATE email_templates
            SET name = ?,
                description = ?,
                html_content = ?,
                subject = ?,
                updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param(
            'ssssii',
            $name,
            $description,
            $html_content,
            $subject,
            $update_id,
            $userId
        );
    } else {
        // Neues Template erstellen
        $stmt = $db->prepare("
            INSERT INTO email_templates
            (name, description, html_content, subject, created_at, user_id)
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param(
            'ssssi',
            $name,
            $description,
            $html_content,
            $subject,
            $userId
        );
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Template wurde gespeichert',
            'template_id' => $update_id ?: $db->insert_id
        ]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Speichern: ' . $e->getMessage()
    ]);
}

$db->close();