<?php
require_once(__DIR__ . '/../n_config.php');

$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;

$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_jobs,
        SUM(CASE 
            WHEN status IN ('send', 'open', 'click', 'failed', 'bounce', 'spam', 'blocked', 'unsub') 
            THEN 1 
            ELSE 0 
        END) as completed_jobs
    FROM email_jobs 
    WHERE content_id = ?
");

$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'is_sending' => ($result['total_jobs'] > $result['completed_jobs'])
]);

$db->close();