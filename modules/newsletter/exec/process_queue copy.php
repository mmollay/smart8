<?php
if (php_sapi_name() !== 'cli') {
    die('Dieses Script kann nur über die Kommandozeile ausgeführt werden');
}

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/EmailService.php';
require_once BASE_PATH . '/classes/PlaceholderService.php';

function writeLog($message)
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
}

// Basis-Überprüfungen
if (empty($mailjetConfig['api_key']) || empty($mailjetConfig['api_secret'])) {
    writeLog("Fehler: Mailjet Konfiguration fehlt");
    exit(1);
}

// Services initialisieren
try {
    $emailService = new EmailService(
        $db,
        $mailjetConfig['api_key'],
        $mailjetConfig['api_secret'],
        $uploadBasePath
    );

    // PlaceholderService als Singleton initialisieren
    $placeholderService = PlaceholderService::getInstance();

    writeLog("Services erfolgreich initialisiert");
} catch (Exception $e) {
    writeLog("Fehler bei Service-Initialisierung: " . $e->getMessage());
    exit(1);
}

function processNewsletter($db, EmailService $emailService, PlaceholderService $placeholderService, $contentId)
{
    $sql = "SELECT ej.*, 
                   ec.subject, 
                   ec.message,
                   s.email as sender_email,
                   s.first_name as sender_first_name,
                   s.last_name as sender_last_name,
                   r.email as recipient_email,
                   r.first_name as recipient_first_name,
                   r.last_name as recipient_last_name,
                   r.gender as recipient_gender,
                   r.title as recipient_title,
                   r.company as recipient_company
            FROM email_jobs ej
            JOIN email_contents ec ON ej.content_id = ec.id
            JOIN senders s ON ej.sender_id = s.id
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE ej.content_id = ? 
            AND ej.status = 'pending'
            LIMIT 50";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        writeLog("SQL Fehler: " . $db->error);
        return 0;
    }

    $stmt->bind_param("i", $contentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        writeLog("Keine ausstehenden E-Mails für Newsletter ID: $contentId");
        return 0;
    }

    $count = 0;
    while ($job = $result->fetch_assoc()) {
        try {
            writeLog("Verarbeite E-Mail für: " . $job['recipient_email']);

            // Platzhalter vorbereiten
            $placeholders = [
                'vorname' => $job['recipient_first_name'],
                'nachname' => $job['recipient_last_name'],
                'email' => $job['recipient_email'],
                'firma' => $job['recipient_company'],
                'geschlecht' => $job['recipient_gender'],
                'titel' => $job['recipient_title']
            ];

            // Betreff und Nachricht mit Platzhaltern ersetzen
            $subject = $placeholderService->replacePlaceholders($job['subject'], $placeholders);
            $message = $placeholderService->replacePlaceholders($job['message'], $placeholders);

            // Abmelde-Link hinzufügen
            $unsubscribeUrl = "https://" . $_SERVER['HTTP_HOST'] . "/unsubscribe.php?email=" .
                urlencode($job['recipient_email']) . "&id=" . $job['id'];
            $message .= "<br><br><hr><p style='font-size: 12px; color: #666;'>
                        Falls Sie keine weiteren E-Mails erhalten möchten, 
                        können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.</p>";

            // Debug Log für Nachrichteninhalt
            writeLog("Nachrichteninhalt (gekürzt): " . substr($message, 0, 100) . "...");

            // E-Mail senden
            $sendResult = $emailService->sendSingleEmail(
                $job['content_id'],
                [
                    'email' => $job['sender_email'],
                    'name' => trim($job['sender_first_name'] . ' ' . $job['sender_last_name'])
                ],
                [
                    'email' => $job['recipient_email'],
                    'name' => trim($job['recipient_first_name'] . ' ' . $job['recipient_last_name'])
                ],
                $subject,
                $message,
                $job['id']
            );

            if ($sendResult['success']) {
                // Status aktualisieren
                $updateSql = "UPDATE email_jobs 
                             SET status = 'send',
                                 sent_at = NOW(),
                                 message_id = ?,
                                 updated_at = NOW()
                             WHERE id = ?";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->bind_param("si", $sendResult['message_id'], $job['id']);
                $updateStmt->execute();

                $count++;
                writeLog("E-Mail erfolgreich gesendet an: " . $job['recipient_email']);
            } else {
                throw new Exception($sendResult['error']);
            }

            usleep(100000); // 100ms Pause

        } catch (Exception $e) {
            writeLog("Fehler beim Senden an {$job['recipient_email']}: " . $e->getMessage());

            // Fehler in der Datenbank speichern
            $errorSql = "UPDATE email_jobs 
                        SET status = 'failed',
                            error_message = ?,
                            updated_at = NOW()
                        WHERE id = ?";
            $errorStmt = $db->prepare($errorSql);
            $errorMessage = substr($e->getMessage(), 0, 255);
            $errorStmt->bind_param("si", $errorMessage, $job['id']);
            $errorStmt->execute();
        }
    }

    return $count;
}

// Hauptprogramm
try {
    $options = getopt('', ['content-id:']);
    $contentId = isset($options['content-id']) ? intval($options['content-id']) : null;

    if ($contentId) {
        writeLog("Verarbeite Newsletter ID: $contentId");
        $count = processNewsletter($db, $emailService, $placeholderService, $contentId);
        writeLog("Newsletter verarbeitet. $count E-Mails gesendet.");
    } else {
        $sql = "SELECT DISTINCT content_id 
                FROM email_jobs 
                WHERE status = 'pending'";
        $result = $db->query($sql);

        $totalCount = 0;
        while ($row = $result->fetch_assoc()) {
            $count = processNewsletter($db, $emailService, $placeholderService, $row['content_id']);
            $totalCount += $count;
        }

        writeLog("Verarbeitung abgeschlossen. Gesamt verarbeitet: $totalCount");
    }

} catch (Exception $e) {
    writeLog("Kritischer Fehler: " . $e->getMessage());
    exit(1);
}