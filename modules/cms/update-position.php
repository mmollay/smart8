<?
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['itemId'] ?? 0;
    $newPosition = $_POST['position'] ?? 0;

    try {
        $stmt = $db->prepare("UPDATE drag_items SET position = ? WHERE id = ?");
        $stmt->execute([$newPosition, $itemId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}