<?php
require_once(__DIR__ . '/../n_config.php');

header('Content-Type: application/json');

if (!isset($_GET['content_id'])) {
    die(json_encode(['error' => 'Keine content_id angegeben']));
}

$contentId = (int) $_GET['content_id'];

try {
    // Prüfe ob der Newsletter dem User gehört
    $stmt = $db->prepare("
        SELECT id 
        FROM email_contents 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $contentId, $userId);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception('Keine Berechtigung');
    }

    // Hole alle Tracking-Events für diesen Newsletter
    $query = "
        SELECT 
            et.*,
            r.email,
            r.first_name,
            r.last_name,
            ej.message_id
        FROM email_tracking et
        JOIN email_jobs ej ON et.job_id = ej.id
        JOIN recipients r ON et.recipient_id = r.id
        WHERE ej.content_id = ?
        ORDER BY et.created_at DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $contentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Format event data
        $eventData = json_decode($row['event_data'], true);

        $events[] = [
            'id' => $row['id'],
            'recipient' => $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')',
            'event_type' => $row['event_type'],
            'timestamp' => $row['created_at'],
            'details' => [
                'url' => $eventData['url'] ?? null,
                'ip' => $eventData['ip'] ?? null,
                'user_agent' => $eventData['user_agent'] ?? null
            ]
        ];
    }

    echo json_encode([
        'success' => true,
        'events' => $events
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}