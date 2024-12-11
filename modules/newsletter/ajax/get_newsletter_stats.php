<?php
include '../n_config.php';

$contentId = intval($_GET['content_id']);

$query = "
SELECT 
    COUNT(DISTINCT recipient_id) as total_recipients,
    SUM(CASE 
        WHEN status = 'send' THEN 1 
        ELSE 0 
    END) as sent_count,
    SUM(CASE 
        WHEN status = 'open' THEN 1 
        ELSE 0 
    END) as opened_count,
    SUM(CASE 
        WHEN status = 'click' THEN 1 
        ELSE 0 
    END) as clicked_count,
    SUM(CASE 
        WHEN status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 
        ELSE 0 
    END) as failed_count,
    SUM(CASE 
        WHEN status = 'unsub' THEN 1 
        ELSE 0 
    END) as unsub_count,
    SUM(CASE 
        WHEN status = 'skipped' AND error_message LIKE '%Blacklist%' THEN 1 
        ELSE 0 
    END) as blacklisted_count
FROM email_jobs 
WHERE content_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $contentId);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $stats
]);