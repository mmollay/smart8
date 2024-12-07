<?php
header('Content-Type: application/json');
include(__DIR__ . '/../n_config.php');

$contentId = intval($_GET['content_id']);
$response = ['success' => false];

try {

    $query = "
        SELECT 
            COUNT(DISTINCT ej.recipient_id) as total_recipients,
            SUM(CASE WHEN ej.status = 'send' THEN 1 ELSE 0 END) as sent_count,
            SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened_count,
            SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked_count,
            SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END) as failed_count,
            SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count
        FROM 
            email_jobs ej
        WHERE 
            ej.content_id = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $contentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();

    $response = array_merge(['success' => true], $stats);

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);