<?php
require_once(__DIR__ . '/../e_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    $update_id = isset($_POST['update_id']) ? intval($_POST['update_id']) : null;

    $data = [
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'status' => intval($_POST['status'])
    ];

    // Validierung
    if (empty($data['title'])) {
        throw new Exception('Titel ist erforderlich');
    }

    $db->begin_transaction();

    if ($update_id) {
        $stmt = $db->prepare("
            UPDATE items 
            SET title = ?, description = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssii",
            $data['title'],
            $data['description'],
            $data['status'],
            $update_id
        );
    } else {
        $stmt = $db->prepare("
            INSERT INTO items (title, description, status, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param(
            "ssi",
            $data['title'],
            $data['description'],
            $data['status']
        );
    }

    $stmt->execute();
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Eintrag erfolgreich gespeichert'
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}