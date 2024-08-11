<?php
require_once (__DIR__ . '/../n_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newsletterId = $_POST['newsletter_id'];
    $fileName = $_POST['file_name'];
    $fileSize = $_POST['file_size'];
    $fileType = $_POST['file_type'];

    $stmt = $db->prepare("INSERT INTO newsletter_attachments (newsletter_id, file_name, file_size, file_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $newsletterId, $fileName, $fileSize, $fileType);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attachment info saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving attachment info']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>