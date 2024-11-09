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
    $stmt = $db->prepare("
        SELECT id, name, description, html_content, subject 
        FROM email_templates 
        WHERE id = ?
    ");

    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($template = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => $template
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Template nicht gefunden'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden: ' . $e->getMessage()
    ]);
}

$db->close();