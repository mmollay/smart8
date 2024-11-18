<?php
require_once '../n_config.php';
require_once '../classes/EmailQueueManager.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['content_id'])) {
        throw new Exception('Keine Newsletter-ID übermittelt');
    }

    $contentId = intval($_POST['content_id']);

    // Prüfe ob Newsletter existiert
    $stmt = $db->prepare("
        SELECT id, send_status 
        FROM email_contents 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $contentId);
    $stmt->execute();
    $newsletter = $stmt->get_result()->fetch_assoc();

    if (!$newsletter) {
        throw new Exception('Newsletter nicht gefunden');
    }

    if ($newsletter['send_status'] == 1) {
        throw new Exception('Newsletter wurde bereits versendet');
    }

    // Erstelle Queue Manager
    $queueManager = new EmailQueueManager($db);

    // Erstelle Queue
    $queueManager->createQueue($contentId);

    // Starte Verarbeitung
    $queueManager->startProcessing();

    echo json_encode([
        'success' => true,
        'message' => 'Massenversand wurde gestartet'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}