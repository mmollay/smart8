<?php
/**
 * Newsletter Versand Script
 * Nutzbar sowohl als Cron-Job als auch für manuellen Versand
 */
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$startTime = microtime(true);

// Basis-Einstellungen
set_time_limit(300);
ini_set('memory_limit', '256M');
date_default_timezone_set('Europe/Vienna');

$isCron = php_sapi_name() === 'cli';

$response = [
    'success' => false,
    'message' => '',
    'statistics' => [],
    'log' => []
];

define('BASE_PATH', dirname(__DIR__));
$logDir = BASE_PATH . '/logs';
$tmpDir = BASE_PATH . '/tmp';

function writeLog($message, $type = 'INFO')
{
    global $logDir, $isCron, $response;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type] $message";

    if ($isCron) {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/cron_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
    }
    $response['log'][] = $logMessage;
}

writeLog("Start des Newsletter-Versands");

try {
    require_once(BASE_PATH . '/n_config.php');
    require_once(BASE_PATH . '/classes/EmailService.php');
    require_once(BASE_PATH . '/classes/PlaceholderService.php');

    // API-Konfiguration prüfen
    if (empty($mailjetConfig['api_key']) || empty($mailjetConfig['api_secret'])) {
        throw new Exception('Mailjet API Konfiguration fehlt');
    }

    // Hole den aktuellsten aktiven Newsletter für den aktuellen User
    $stmt = $db->prepare("
        SELECT DISTINCT ec.id as content_id
        FROM email_jobs ej
        JOIN email_contents ec ON ej.content_id = ec.id
        WHERE ej.status = 'pending'
        AND ec.user_id = ?
        ORDER BY ej.created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $activeNewsletter = $result->fetch_assoc();

    if (!$activeNewsletter) {
        $message = "Keine ausstehenden Newsletter gefunden";
        writeLog($message);
        $response['message'] = $message;
        throw new Exception($message);
    }

    $content_id = $activeNewsletter['content_id'];
    writeLog("Aktiver Newsletter gefunden: ID " . $content_id);

    // Services initialisieren
    $emailService = new EmailService(
        $db,
        $mailjetConfig['api_key'],
        $mailjetConfig['api_secret'],
        $uploadBasePath
    );
    $placeholderService = PlaceholderService::getInstance();

    // Ausstehende E-Mails für den aktuellen User abrufen
    $stmt = $db->prepare("
        SELECT 
            ej.*,
            ec.subject,
            ec.message,
            s.email as sender_email,
            s.first_name as sender_first_name,
            s.last_name as sender_last_name,
            CONCAT(s.first_name, ' ', s.last_name) as sender_name,
            s.company as sender_company,
            r.email as recipient_email,
            r.first_name as recipient_first_name, 
            r.last_name as recipient_last_name,
            r.company as recipient_company,
            r.gender as recipient_gender,
            r.title as recipient_title
        FROM email_jobs ej
        JOIN email_contents ec ON ej.content_id = ec.id  
        JOIN senders s ON ej.sender_id = s.id
        JOIN recipients r ON ej.recipient_id = r.id
        WHERE ej.status = 'pending'
        AND ec.user_id = ?
        AND ec.id = ?
        AND s.user_id = ?
        AND r.user_id = ?
        ORDER BY ej.created_at ASC
        LIMIT 50
    ");

    $stmt->bind_param("iiii", $userId, $content_id, $userId, $userId);
    $stmt->execute();
    $jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $totalJobs = count($jobs);

    writeLog("Gefunden: $totalJobs ausstehende E-Mails für Newsletter ID: $content_id");

    if ($totalJobs === 0) {
        $message = "Keine ausstehenden E-Mails gefunden";
        writeLog($message);
        $response['success'] = true;
        $response['message'] = $message;
        throw new Exception($message);
    }

    $successCount = 0;
    $failCount = 0;
    $errors = [];

    foreach ($jobs as $job) {
        try {
            writeLog("Verarbeite E-Mail für: " . $job['recipient_email']);

            // Platzhalter für aktuellen Empfänger erstellen
            $recipientPlaceholders = $placeholderService->createPlaceholders([
                'first_name' => $job['recipient_first_name'],
                'last_name' => $job['recipient_last_name'],
                'email' => $job['recipient_email'],
                'company' => $job['recipient_company'],
                'gender' => $job['recipient_gender'],
                'title' => $job['recipient_title']
            ]);

            // Platzhalter ersetzen
            $subject = $placeholderService->replacePlaceholders($job['subject'], $recipientPlaceholders);
            $message = $placeholderService->replacePlaceholders($job['message'], $recipientPlaceholders);

            // Abmelde-Link hinzufügen
            $unsubscribeUrl = "https://" . $_SERVER['HTTP_HOST'] . "/unsubscribe.php?email=" .
                urlencode($job['recipient_email']) . "&id=" . $job['id'];
            $unsubscribeLink = "<br><br><hr><p style='font-size: 12px; color: #666;'>
                Falls Sie keine weiteren E-Mails erhalten möchten, 
                können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.</p>";
            $message .= $unsubscribeLink;

            // Absender- und Empfängerdaten vorbereiten
            $sender = [
                'email' => $job['sender_email'],
                'name' => $job['sender_name']
            ];

            $recipient = [
                'email' => $job['recipient_email'],
                'name' => "{$job['recipient_first_name']} {$job['recipient_last_name']}"
            ];

            // E-Mail senden
            $result = $emailService->sendSingleEmail(
                $job['content_id'],
                $sender,
                $recipient,
                $subject,
                $message,
                $job['id']
            );

            if ($result['success']) {
                // Job-Status aktualisieren
                $stmt = $db->prepare("
                    UPDATE email_jobs 
                    SET 
                        status = 'send',
                        sent_at = NOW(),
                        message_id = ?,
                        updated_at = NOW()
                    WHERE id = ?
                    AND content_id IN (SELECT id FROM email_contents WHERE user_id = ?)
                ");
                $stmt->bind_param("sii", $result['message_id'], $job['id'], $userId);
                $stmt->execute();

                // Status loggen
                $stmt = $db->prepare("
                    INSERT INTO status_log 
                    (event, timestamp, message_id, email) 
                    VALUES ('send', NOW(), ?, ?)
                ");
                $stmt->bind_param("ss", $result['message_id'], $job['recipient_email']);
                $stmt->execute();

                $successCount++;
                writeLog("E-Mail erfolgreich gesendet an: " . $job['recipient_email'], "SUCCESS");
            } else {
                throw new Exception($result['error'] ?? 'Unbekannter Fehler');
            }

            usleep(100000); // 100ms Pause zwischen E-Mails

        } catch (Exception $e) {
            $failCount++;
            $errorMessage = $e->getMessage();
            $errors[] = "Fehler bei {$job['recipient_email']}: $errorMessage";
            writeLog("Fehler beim Versand an {$job['recipient_email']}: $errorMessage", "ERROR");

            // Fehler in der Datenbank speichern
            $stmt = $db->prepare("
                UPDATE email_jobs 
                SET 
                    status = 'failed',
                    error_message = ?,
                    updated_at = NOW()
                WHERE id = ?
                AND content_id IN (SELECT id FROM email_contents WHERE user_id = ?)
            ");
            $errorMessageDb = substr($errorMessage, 0, 255);
            $stmt->bind_param("sii", $errorMessageDb, $job['id'], $userId);
            $stmt->execute();
        }
    }

    // Newsletter-Status aktualisieren wenn alle Jobs abgearbeitet sind
    $stmt = $db->prepare("
        SELECT COUNT(*) as remaining
        FROM email_jobs ej
        JOIN email_contents ec ON ej.content_id = ec.id
        WHERE ec.id = ?
        AND ec.user_id = ?
        AND ej.status = 'pending'
    ");
    $stmt->bind_param("ii", $content_id, $userId);
    $stmt->execute();
    $remaining = $stmt->get_result()->fetch_assoc()['remaining'];

    if ($remaining == 0) {
        $stmt = $db->prepare("
            UPDATE email_contents 
            SET send_status = 2
            WHERE id = ?
            AND user_id = ?
        ");
        $stmt->bind_param("ii", $content_id, $userId);
        $stmt->execute();
        writeLog("Newsletter ID $content_id vollständig versendet");
    }

    writeLog("Versand abgeschlossen. Erfolge: $successCount, Fehler: $failCount");

    $response['success'] = true;
    $response['message'] = "Versand gestartet";
    $response['statistics'] = [
        'content_id' => $content_id,
        'total_jobs' => $totalJobs,
        'success_count' => $successCount,
        'fail_count' => $failCount,
        'duration_seconds' => number_format(microtime(true) - $startTime, 2)
    ];

} catch (Exception $e) {
    writeLog("Kritischer Fehler: " . $e->getMessage(), "CRITICAL");
    if (!isset($response['message'])) {
        $response['message'] = "Fehler: " . $e->getMessage();
    }

    if (isset($adminEmail)) {
        mail(
            $adminEmail,
            'Kritischer Fehler im Newsletter-Versand',
            "Fehler im Newsletter-Versand:\n\n" . $e->getMessage()
        );
    }
} finally {
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }

    $output = ob_get_clean();
    if (!empty($output)) {
        error_log("Unerwartete Ausgabe vor JSON: " . $output);
    }

    if (!$isCron) {
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}