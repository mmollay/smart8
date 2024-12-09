<?php
require_once(__DIR__ . '/../n_config.php');

header('Content-Type: application/json');

if (!isset($_POST['content_id'])) {
    die(json_encode(['success' => false, 'message' => 'Keine Newsletter-ID übermittelt']));
}

$content_id = intval($_POST['content_id']);

try {
    // Prüfen ob der Newsletter dem User gehört
    $stmt = $db->prepare("SELECT id, send_status FROM email_contents WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $content_id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result->num_rows) {
        throw new Exception('Keine Berechtigung');
    }

    $newsletter = $result->fetch_assoc();

    // Transaktion starten
    $db->begin_transaction();

    // Alle Jobs für diesen Newsletter löschen
    $stmt = $db->prepare("DELETE FROM email_jobs WHERE content_id = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();

    // Newsletter-Status zurücksetzen
    $stmt = $db->prepare("
        UPDATE email_contents 
        SET send_status = 0,
            completed_at = NULL
        WHERE id = ?
    ");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();

    // Falls ein Cron läuft, diesen auch beenden
    $stmt = $db->prepare("
        UPDATE cron_status 
        SET status = 'error',
            error_messages = 'Versand manuell abgebrochen',
            end_time = NOW()
        WHERE content_id = ? 
        AND status = 'running'
    ");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();

    $db->commit();

    // Log erstellen
    $logMessage = "Newsletter #$content_id: Versand manuell abgebrochen";
    error_log($logMessage, 3, __DIR__ . '/../logs/newsletter.log');

    echo json_encode([
        'success' => true,
        'message' => 'Versand wurde gestoppt und zurückgesetzt'
    ]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}