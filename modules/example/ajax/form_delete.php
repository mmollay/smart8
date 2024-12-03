<?php
require_once(__DIR__ . '/../e_config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    $delete_id = intval($_POST['delete_id']);

    if (!$delete_id) {
        throw new Exception('Ungültige ID');
    }

    $db->begin_transaction();

    $stmt = $db->prepare("DELETE FROM items WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Eintrag nicht gefunden');
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Eintrag erfolgreich gelöscht'
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
