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
        // Update existierendes Template
        $stmt = $db->prepare("
            UPDATE email_templates 
            SET name = ?, 
                description = ?, 
                html_content = ?, 
                subject = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->bind_param(
            'ssssi',
            $name,
            $description,
            $html_content,
            $subject,
            $update_id
        );
    } else {
        // Neues Template erstellen
        $stmt = $db->prepare("
            INSERT INTO email_templates 
            (name, description, html_content, subject, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            'ssss',
            $name,
            $description,
            $html_content,
            $subject
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