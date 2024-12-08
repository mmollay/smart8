<?php
require_once(__DIR__ . '/../n_config.php');
header('Content-Type: application/json');

try {
    $stmt = $db->prepare("SELECT id, name, color FROM groups WHERE user_id = ? AND is_active = 1 ORDER BY name");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'groups' => $groups
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>