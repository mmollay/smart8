<?php
/**
 * Newsletter Versand Script
 * Nutzbar sowohl als Cron-Job als auch für manuellen Versand
 */
// Verhindere direkte Ausgaben vor dem JSON-Response
ob_start();

// Fehlerberichterstattung für Debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keine Fehler direkt ausgeben
ini_set('log_errors', 1);     // Fehler loggen

// Start time für Performance-Tracking
$startTime = microtime(true);

// Basis-Einstellungen
set_time_limit(300); // 5 Minuten maximale Ausführungszeit
ini_set('memory_limit', '256M');
date_default_timezone_set('Europe/Vienna');

// Erkennung ob Cron oder manueller Aufruf
$isCron = php_sapi_name() === 'cli';

// Response-Array für manuellen Aufruf
$response = [
    'success' => false,
    'message' => '',
    'statistics' => [],
    'log' => []
];

// Pfade definieren
define('BASE_PATH', dirname(__DIR__));
$logDir = BASE_PATH . '/logs';
$tmpDir = BASE_PATH . '/tmp';

// Logging-Funktion
function writeLog($message, $type = 'INFO')
{
    global $logDir, $isCron, $response;

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type] $message";

    // Für Cron in Datei schreiben
    if ($isCron) {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/cron_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
    }

    // Für manuellen Aufruf ins Response-Array
    $response['log'][] = $logMessage;
}

// Start des Scripts
writeLog("Start des Newsletter-Versands");

try {
    // Erforderliche Dateien einbinden
    require_once(BASE_PATH . '/n_config.php');
    require_once(BASE_PATH . '/classes/EmailService.php');
    require_once(BASE_PATH . '/classes/PlaceholderService.php');

    // Hole den aktuellsten aktiven Newsletter
    $result = $db->query("
        SELECT DISTINCT ec.id as content_id
        FROM email_jobs ej
        JOIN email_contents ec ON ej.content_id = ec.id
        WHERE ej.status = 'pending'
        ORDER BY ej.created_at DESC
        LIMIT 1
    ");

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
    $emailService = new EmailService($db, $apiKey, $apiSecret, $uploadBasePath);
    $placeholderService = PlaceholderService::getInstance();

    // Ausstehende E-Mails abrufen
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
        AND ej.content_id = ?
        ORDER BY ej.created_at ASC
        LIMIT 50
    ");

    $stmt->bind_param("i", $content_id);
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
                ");
                $stmt->bind_param("si", $result['message_id'], $job['id']);
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

            // Kleine Pause zwischen E-Mails
            usleep(100000); // 100ms

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
            ");
            $errorMessageDb = substr($errorMessage, 0, 255);
            $stmt->bind_param("si", $errorMessageDb, $job['id']);
            $stmt->execute();
        }
    }

    // Newsletter-Status aktualisieren wenn alle Jobs abgearbeitet sind
    $stmt = $db->prepare("
        SELECT COUNT(*) as remaining
        FROM email_jobs
        WHERE content_id = ?
        AND status = 'pending'
    ");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $remaining = $stmt->get_result()->fetch_assoc()['remaining'];

    if ($remaining == 0) {
        $stmt = $db->prepare("
            UPDATE email_contents 
            SET send_status = 1
            WHERE id = ?
        ");
        $stmt->bind_param("i", $content_id);
        $stmt->execute();
        writeLog("Newsletter ID $content_id vollständig versendet");
    }

    writeLog("Versand abgeschlossen. Erfolge: $successCount, Fehler: $failCount");

    // Response für manuellen Aufruf vorbereiten
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

    // Admin benachrichtigen
    if (isset($adminEmail)) {
        mail(
            $adminEmail,
            'Kritischer Fehler im Newsletter-Versand',
            "Fehler im Newsletter-Versand:\n\n" . $e->getMessage()
        );
    }

} finally {
    // Aufräumen
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }

    // Puffer leeren und Output kontrollieren
    $output = ob_get_clean();
    if (!empty($output)) {
        // Wenn es unerwartete Ausgaben gab, diese loggen
        error_log("Unerwartete Ausgabe vor JSON: " . $output);
    }

    // Ausgabe für manuellen Aufruf
    if (!$isCron) {
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}