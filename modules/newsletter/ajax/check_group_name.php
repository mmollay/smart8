<?php
// ajax/check_group_name.php
require_once(__DIR__ . '/../n_config.php');

header('Content-Type: application/json');

try {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        throw new Exception('Kein Gruppenname angegeben');
    }

    // Prüfe ob Gruppe bereits existiert
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM `groups` WHERE LOWER(name) = LOWER(?)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();

    echo json_encode([
        'success' => true,
        'exists' => $count > 0
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($db)) {
    $db->close();
}
?>
