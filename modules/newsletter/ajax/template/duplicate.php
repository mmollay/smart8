<?php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

$template_id = $_POST['template_id'] ?? null;

if (!$template_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Template ID fehlt'
    ]);
    exit;
}

try {
    // Original Template laden
    $stmt = $db->prepare("SELECT name, description, html_content, subject FROM email_templates WHERE id = ?");
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();

    if (!$template) {
        throw new Exception('Template nicht gefunden');
    }

    // Kopie erstellen
    $stmt = $db->prepare("
        INSERT INTO email_templates 
        (name, description, html_content, subject, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    $copyName = $template['name'] . ' (Kopie)';
    $stmt->bind_param(
        'ssss',
        $copyName,
        $template['description'],
        $template['html_content'],
        $template['subject']
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Template wurde dupliziert',
            'new_id' => $db->insert_id
        ]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Duplizieren: ' . $e->getMessage()
    ]);
}

$db->close();