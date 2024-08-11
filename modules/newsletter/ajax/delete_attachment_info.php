<?php
require_once (__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attachmentId = $_POST['attachment_id'];

    $stmt = $db->prepare("DELETE FROM newsletter_attachments WHERE id = ?");
    $stmt->bind_param("i", $attachmentId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attachment info deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting attachment info']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>