<?php
require_once '../n_config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['content_id'])) {
        throw new Exception('Keine Newsletter-ID übermittelt');
    }

    $contentId = intval($_GET['content_id']);

    // Status aller Jobs für diesen Newsletter abrufen
    $query = "
        SELECT 
            COUNT(*) as total_emails,
            SUM(CASE WHEN status = 'send' THEN 1 ELSE 0 END) as processed_emails,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_emails
        FROM email_jobs
        WHERE content_id = ?
    ";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception('Datenbankfehler: ' . $db->error);
    }

    $stmt->bind_param("i", $contentId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // Berechne den Fortschritt
    $total = (int) $result['total_emails'];
    $processed = (int) $result['processed_emails'];
    $failed = (int) $result['failed_emails'];

    $percentage = $total > 0 ? round((($processed + $failed) / $total) * 100, 2) : 0;

    // Prüfe ob der Versand abgeschlossen ist
    $is_completed = ($processed + $failed) >= $total;

    // Wenn abgeschlossen, aktualisiere den Newsletter-Status
    if ($is_completed) {
        $updateStmt = $db->prepare("
            UPDATE email_contents 
            SET send_status = 1
            WHERE id = ? AND send_status = 0
        ");
        $updateStmt->bind_param("i", $contentId);
        $updateStmt->execute();
    }

    $response = [
        'success' => true,
        'total_emails' => $total,
        'processed_emails' => $processed,
        'failed_emails' => $failed,
        'percentage' => $percentage,
        'is_completed' => $is_completed
    ];

    // Debug-Informationen hinzufügen
    if (isset($_GET['debug'])) {
        $response['debug'] = [
            'sql' => $query,
            'content_id' => $contentId,
            'raw_result' => $result
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Schließe die Datenbankverbindung
if (isset($db)) {
    $db->close();
}