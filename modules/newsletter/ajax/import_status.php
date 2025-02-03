<?php
require_once(__DIR__ . '/../n_config.php');

//session_start();
$import_id = $_SESSION['current_import_id'] ?? null;

if (!$import_id) {
    die(json_encode(['error' => 'Kein aktiver Import']));
}

$sql = "SELECT total_records, processed_records, imported, updated, skipped, status 
        FROM import_progress 
        WHERE id = ? AND user_id = ?";

$stmt = $db->prepare($sql);
$stmt->bind_param('ii', $import_id, $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode($result);