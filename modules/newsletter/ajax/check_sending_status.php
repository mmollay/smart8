<?php
// ajax/check_sending_status.php
include(__DIR__ . '/../n_config.php');

$contentId = (int) $_GET['content_id'];

try {
    // Prüfe ob der User Zugriff auf diesen Newsletter hat
    $checkAccess = $db->prepare("SELECT id FROM email_contents WHERE id = ? AND user_id = ?");
    $checkAccess->bind_param('ii', $contentId, $userId);
    $checkAccess->execute();
    if (!$checkAccess->get_result()->fetch_assoc()) {
        throw new Exception('Keine Berechtigung');
    }

    $query = "SELECT 
        COUNT(DISTINCT ej.recipient_id) as total_recipients,
        SUM(CASE 
            WHEN ej.status IN ('send', 'open', 'click', 'delivered') 
            THEN 1 
            ELSE 0 
        END) as sent_count,
        SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count,
        SUM(CASE 
            WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') 
            THEN 1 
            ELSE 0 
        END) as failed_count
    FROM email_jobs ej 
    WHERE ej.content_id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $contentId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'total' => (int) $result['total_recipients'],
        'sent' => (int) $result['sent_count'],
        'unsub' => (int) $result['unsub_count'],
        'failed' => (int) $result['failed_count']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}