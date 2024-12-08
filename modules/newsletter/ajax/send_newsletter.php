<?php
include(__DIR__ . '/../n_config.php');
header('Content-Type: application/json');

function sendJsonResponse($status, $message)
{
    // Konvertiere den Status in ein success-Boolean für die JavaScript-Seite
    $response = [
        'success' => ($status === 'success'),  // true wenn success, false wenn error
        'message' => $message
    ];
    echo json_encode($response);
    exit;
}

if (!isset($_POST['content_id'])) {
    sendJsonResponse('error', 'Keine content_id übergeben.');
}

$content_id = intval($_POST['content_id']);

try {
    $db->begin_transaction();

    // Überprüfen, ob der Newsletter bereits gesendet wird
    $stmt = $db->prepare("SELECT send_status FROM email_contents WHERE id = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $stmt->bind_result($send_status);
    $stmt->fetch();
    $stmt->close();

    if ($send_status != 0) {
        throw new Exception('Dieser Newsletter wird bereits gesendet.');
    }

    // Setze den send_status auf 1
    $stmt = $db->prepare("UPDATE email_contents SET send_status = 1 WHERE id = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $stmt->close();


    $sql = "
    INSERT INTO email_jobs (content_id, sender_id, recipient_id, status, error_message)
    SELECT DISTINCT 
        ec.id, 
        ec.sender_id, 
        rg.recipient_id, 
        CASE 
            WHEN b.id IS NOT NULL THEN 'skipped'
            ELSE 'pending'
        END as status,
        CASE 
            WHEN b.id IS NOT NULL THEN CONCAT('E-Mail steht auf Blacklist. Grund: ', b.reason)
            ELSE NULL
        END as error_message
    FROM email_contents ec
    JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
    JOIN recipient_group rg ON ecg.group_id = rg.group_id
    JOIN recipients r ON rg.recipient_id = r.id
    LEFT JOIN blacklist b ON r.email = b.email AND b.user_id = r.user_id
    WHERE ec.id = ?
    AND r.unsubscribed = 0  -- Nur nicht abgemeldete Empfänger
    AND NOT EXISTS (
        SELECT 1 FROM email_jobs ej
        WHERE ej.content_id = ec.id
        AND ej.sender_id = ec.sender_id
        AND ej.recipient_id = rg.recipient_id
    )";


    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows == 0) {
        throw new Exception('Keine neuen Jobs zum Senden gefunden.');
    }

    // Log-Einträge für den Start des Versandprozesses
    $stmt = $db->prepare("
        INSERT INTO email_logs (job_id, status, response) 
        SELECT id, 'send', 'Versand gestartet' 
        FROM email_jobs 
        WHERE content_id = ?
    ");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $stmt->close();

    $db->commit();
    sendJsonResponse('success', 'Newsletter wird gesendet. Anzahl der Jobs: ' . $affected_rows);

} catch (Exception $e) {
    $db->rollback();
    sendJsonResponse('error', $e->getMessage());
} finally {
    $db->close();
}