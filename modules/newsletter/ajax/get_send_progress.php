<?php
include(__DIR__ . '/../n_config.php');
header('Content-Type: application/json');

if (!isset($_GET['content_id'])) {
    echo json_encode(['success' => false, 'message' => 'Keine content_id übergeben']);
    exit;
}

$content_id = intval($_GET['content_id']);

try {
    $sql = "
        SELECT 
            cs.status as cron_status,
            COUNT(*) as total,
            SUM(CASE 
                WHEN ej.status IN ('send', 'open', 'click', 'failed', 'bounce', 'spam', 'unsub', 'skipped') 
                THEN 1 
                ELSE 0 
            END) as processed,
            SUM(CASE WHEN ej.status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN ej.status = 'send' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened,
            SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked
        FROM email_jobs ej
        LEFT JOIN cron_status cs ON cs.content_id = ej.content_id 
        WHERE ej.content_id = ?
        GROUP BY cs.status
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'status' => $data['cron_status'] ?? null,
        'total' => (int) $data['total'],
        'processed' => (int) $data['processed'],
        'failed' => (int) $data['failed'],
        'sent' => (int) $data['sent'],
        'opened' => (int) $data['opened'],
        'clicked' => (int) $data['clicked']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$db->close();