<?php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

$template_id = $_POST['delete_id'] ?? null;

if (!$template_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Template ID fehlt'
    ]);
    exit;
}

try {
    // Prüfen ob Template in Benutzung ist
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_contents WHERE template_id = ?");
    $stmt->bind_param('i', $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usage = $result->fetch_assoc()['count'];

    if ($usage > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Template kann nicht gelöscht werden, da es in Verwendung ist'
        ]);
        exit;
    }

    // Template löschen
    $stmt = $db->prepare("DELETE FROM email_templates WHERE id = ?");
    $stmt->bind_param('i', $template_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Template wurde gelöscht'
        ]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Löschen: ' . $e->getMessage()
    ]);
}

$db->close();