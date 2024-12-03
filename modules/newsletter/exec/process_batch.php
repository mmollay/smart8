<?php
// Namespace entfernen, da deine Klassen keinen Namespace haben
if (php_sapi_name() !== 'cli') {
    die('Dieses Script kann nur über die Kommandozeile ausgeführt werden');
}

// Basis-Pfad definieren
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 1); // Für's Debugging auf 1 setzen
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/batch_error.log');

// Zeit- und Speicherlimits
set_time_limit(3600); // 1 Stunde 
ini_set('memory_limit', '256M');

// Erforderliche Dateien einbinden
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/EmailService.php';
require_once BASE_PATH . '/classes/PlaceholderService.php';

// Debugging: Prüfe ob Klassen geladen wurden
if (!class_exists('EmailService')) {
    die("EmailService Klasse nicht gefunden\n");
}

// Kommandozeilenargumente verarbeiten
$options = getopt('', ['content-id:', 'job-ids:']);
if (!isset($options['content-id']) || !isset($options['job-ids'])) {
    die("Erforderliche Parameter fehlen\n");
}

$contentId = (int) $options['content-id'];
$jobIds = explode(',', $options['job-ids']);
$processId = getmypid();

// Logging-Funktion
function writeLog($message, $type = 'INFO')
{
    global $processId;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type][PID:$processId] $message\n";

    $logFile = BASE_PATH . '/logs/batch_process.log';
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

writeLog("Starte Batch-Verarbeitung für Content ID: $contentId mit Jobs: " . implode(',', $jobIds));

try {
    // Services initialisieren
    $emailService = new EmailService(
        $db,
        $mailjetConfig['api_key'],
        $mailjetConfig['api_secret'],
        $uploadBasePath
    );
    $placeholderService = PlaceholderService::getInstance();

    $successCount = 0;
    $errorCount = 0;

    // Verarbeite Jobs
    foreach ($jobIds as $jobId) {
        try {
            processJob($db, $emailService, $placeholderService, $contentId, $jobId);
            $successCount++;
            // Kleine Pause zwischen den Emails
            usleep(100000); // 100ms
        } catch (Exception $e) {
            writeLog("Fehler bei Job ID $jobId: " . $e->getMessage(), 'ERROR');
            $errorCount++;
            continue;
        }
    }

    writeLog("Batch abgeschlossen. Erfolge: $successCount, Fehler: $errorCount");

} catch (Exception $e) {
    writeLog("Kritischer Fehler: " . $e->getMessage(), 'CRITICAL');
    die("Kritischer Fehler: " . $e->getMessage() . "\n");
}

/**
 * Verarbeitet einen einzelnen Email-Job
 */
function processJob($db, $emailService, $placeholderService, $contentId, $jobId)
{
    $jobId = (int) $jobId;  // Sicherstellen dass es eine Zahl ist

    writeLog("Verarbeite Job $jobId");

    // Job-Daten laden
    $stmt = $db->prepare("
        SELECT ej.*, 
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
        WHERE ej.id = ? 
        AND ej.content_id = ?
        AND ej.status = 'pending'
        FOR UPDATE
    ");

    $stmt->bind_param("ii", $jobId, $contentId);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();

    if (!$job) {
        writeLog("Job $jobId nicht gefunden oder nicht mehr pending", 'WARNING');
        return false;
    }

    writeLog("Verarbeite E-Mail für: " . $job['recipient_email']);

    // Platzhalter erstellen
    $placeholders = [
        'vorname' => $job['recipient_first_name'],
        'nachname' => $job['recipient_last_name'],
        'email' => $job['recipient_email'],
        'firma' => $job['recipient_company'],
        'company' => $job['recipient_company'],
        'geschlecht' => $job['recipient_gender'],
        'titel' => $job['recipient_title'],
        'anrede' => getAnrede(
            $job['recipient_gender'],
            $job['recipient_title'],
            $job['recipient_first_name'],
            $job['recipient_last_name']
        )
    ];

    // Platzhalter ersetzen
    $subject = $placeholderService->replacePlaceholders($job['subject'], $placeholders);
    $message = $placeholderService->replacePlaceholders($job['message'], $placeholders);

    // Abmelde-Link hinzufügen
    $host = $_SERVER['HTTP_HOST'] ?? 'deine-domain.de';  // Fallback für CLI
    $unsubscribeUrl = "https://" . $host . "/unsubscribe.php?email=" .
        urlencode($job['recipient_email']) . "&id=" . $job['id'];
    $unsubscribeLink = "<br><br><hr><p style='font-size: 12px; color: #666;'>
        Falls Sie keine weiteren E-Mails erhalten möchten, 
        können Sie sich hier <a href='{$unsubscribeUrl}'>abmelden</a>.</p>";
    $message .= $unsubscribeLink;

    // Absender- und Empfängerdaten
    $sender = [
        'email' => $job['sender_email'],
        'name' => trim($job['sender_first_name'] . ' ' . $job['sender_last_name'])
    ];

    $recipient = [
        'email' => $job['recipient_email'],
        'name' => trim($job['recipient_first_name'] . ' ' . $job['recipient_last_name'])
    ];

    try {
        // Email senden
        $sendResult = $emailService->sendSingleEmail(
            $contentId,
            $sender,
            $recipient,
            $subject,
            $message,
            $job['id']
        );

        if ($sendResult['success']) {
            // Update job status
            $updateStmt = $db->prepare("
                UPDATE email_jobs 
                SET status = 'send',
                    sent_at = NOW(),
                    message_id = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->bind_param("si", $sendResult['message_id'], $job['id']);
            $updateStmt->execute();

            // Log success
            $logStmt = $db->prepare("
                INSERT INTO email_logs 
                (job_id, status, response, created_at) 
                VALUES (?, 'send', 'Email erfolgreich gesendet', NOW())
            ");
            $logStmt->bind_param("i", $job['id']);
            $logStmt->execute();

            writeLog("E-Mail erfolgreich gesendet an: " . $job['recipient_email']);
            return true;
        } else {
            throw new Exception($sendResult['error'] ?? 'Unbekannter Fehler');
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        writeLog("Fehler beim Senden an {$job['recipient_email']}: $errorMessage", 'ERROR');

        // Job als fehlgeschlagen markieren
        $updateStmt = $db->prepare("
            UPDATE email_jobs 
            SET status = 'failed',
                error_message = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $errorMessageDb = substr($errorMessage, 0, 255);
        $updateStmt->bind_param("si", $errorMessageDb, $job['id']);
        $updateStmt->execute();

        // Fehler loggen
        $logStmt = $db->prepare("
            INSERT INTO email_logs 
            (job_id, status, response, created_at) 
            VALUES (?, 'failed', ?, NOW())
        ");
        $logStmt->bind_param("is", $job['id'], $errorMessage);
        $logStmt->execute();

        return false;
    }
}

/**
 * Generiert die Anrede basierend auf den Empfängerdaten
 */
function getAnrede($gender, $title, $firstName, $lastName)
{
    $anrede = 'Sehr ';

    if ($gender === 'female') {
        $anrede .= 'geehrte';
        $anrede .= $title ? ' Frau ' . $title : ' Frau';
    } else {
        $anrede .= 'geehrter';
        $anrede .= $title ? ' Herr ' . $title : ' Herr';
    }

    $anrede .= ' ' . $lastName;

    return $anrede;
}