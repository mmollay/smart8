<?php
if (php_sapi_name() !== 'cli') {
    die('Dieses Script kann nur über die Kommandozeile ausgeführt werden');
}

// Basis-Pfad definieren
define('BASE_PATH', realpath(__DIR__ . '/..'));

// Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/batch_error.log');

// Zeit- und Speicherlimits
set_time_limit(3600); // 1 Stunde 
ini_set('memory_limit', '256M');

// Erforderliche Dateien einbinden
require_once BASE_PATH . '/n_config.php';
require_once BASE_PATH . '/classes/EmailService.php';
require_once BASE_PATH . '/classes/PlaceholderService.php';

// Prüfe ob Klassen geladen wurden
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

// Batch-spezifische Logging-Datei
$batchLogFile = BASE_PATH . "/logs/batch_{$contentId}_" . time() . ".log";

// Logging-Funktion
function writeLog($message, $type = 'INFO', $logFile = null)
{
    global $processId, $batchLogFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$type][PID:$processId] $message\n";

    // Schreibe in die Batch-spezifische Log-Datei
    if ($logFile) {
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    // Schreibe auch in das allgemeine Batch-Log
    file_put_contents(
        BASE_PATH . '/logs/batch_process.log',
        $logMessage,
        FILE_APPEND
    );
}

writeLog("Starte Batch-Verarbeitung für Content ID: $contentId mit Jobs: " . implode(',', $jobIds), 'INFO', $batchLogFile);

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
    $skippedCount = 0;

    // Verarbeite Jobs
    foreach ($jobIds as $jobId) {
        try {
            // Prüfe den Job-Status und den Empfänger-Status
            $checkStmt = $db->prepare("
                SELECT r.unsubscribed, ej.status
                FROM email_jobs ej
                JOIN recipients r ON ej.recipient_id = r.id
                WHERE ej.id = ?
            ");
            $checkStmt->bind_param("i", $jobId);
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();

            if (!$result) {
                writeLog("Job ID $jobId nicht gefunden", 'ERROR', $batchLogFile);
                $errorCount++;
                continue;
            }

            // Überspringe abgemeldete Empfänger
            if ($result['unsubscribed']) {
                writeLog("Job ID $jobId übersprungen - Empfänger abgemeldet", 'INFO', $batchLogFile);
                markJobSkipped($db, $jobId, "Empfänger hat sich abgemeldet");
                $skippedCount++;
                continue;
            }

            // Überspringe Jobs die nicht mehr 'pending' oder 'processing' sind
            if (!in_array($result['status'], ['pending', 'processing'])) {
                writeLog("Job ID $jobId übersprungen - Status ist " . $result['status'], 'INFO', $batchLogFile);
                $skippedCount++;
                continue;
            }

            processJob($db, $emailService, $placeholderService, $contentId, $jobId, $batchLogFile);
            $successCount++;
            usleep(100000); // 100ms Pause zwischen E-Mails
        } catch (Exception $e) {
            writeLog("Fehler bei Job ID $jobId: " . $e->getMessage(), 'ERROR', $batchLogFile);
            markJobFailed($db, $jobId, $e->getMessage());
            $errorCount++;
            continue;
        }
    }

    writeLog("Batch abgeschlossen. Erfolge: $successCount, Fehler: $errorCount, Übersprungen: $skippedCount", 'INFO', $batchLogFile);

} catch (Exception $e) {
    writeLog("Kritischer Fehler: " . $e->getMessage(), 'CRITICAL', $batchLogFile);
    die("Kritischer Fehler: " . $e->getMessage() . "\n");
}

/**
 * Verarbeitet einen einzelnen Email-Job
 */
function processJob($db, $emailService, $placeholderService, $contentId, $jobId, $batchLogFile)
{
    global $_ENV;

    $jobId = (int) $jobId;
    writeLog("Verarbeite Job $jobId", 'INFO', $batchLogFile);

    // Job-Daten laden mit Transaktion
    $db->begin_transaction();

    try {
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
                   r.company as recipient_company,
                   r.unsubscribed
            FROM email_jobs ej
            JOIN email_contents ec ON ej.content_id = ec.id
            JOIN senders s ON ej.sender_id = s.id
            JOIN recipients r ON ej.recipient_id = r.id
            WHERE ej.id = ? 
            AND ej.content_id = ?
            AND ej.status IN ('pending', 'processing')
            FOR UPDATE
        ");

        $stmt->bind_param("ii", $jobId, $contentId);
        $stmt->execute();
        $job = $stmt->get_result()->fetch_assoc();

        if (!$job) {
            throw new Exception("Job $jobId nicht gefunden oder nicht mehr verfügbar");
        }

        // Nochmalige Prüfung des Abmeldestatus
        if ($job['unsubscribed']) {
            $db->commit();
            markJobSkipped($db, $jobId, "Empfänger hat sich abgemeldet");
            return;
        }

        writeLog("Verarbeite E-Mail für: " . $job['recipient_email'], 'INFO', $batchLogFile);

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


        // Korrekte Basis-URL aus Konfiguration
        $APP_URL = $_ENV['APP_URL'] ?? 'https://newsletter.ssi.at'; // Fallback hinzufügen

        // Platzhalter ersetzen und URLs anpassen
        try {
            $subject = $placeholderService->replacePlaceholders($job['subject'], $placeholders);

            $message = $job['message'];
            $message = $placeholderService->replacePlaceholders($message, $placeholders);
            $message = makeUrlsAbsolute($message, $APP_URL);

            writeLog("URLs in der Nachricht erfolgreich ersetzt", 'INFO', $batchLogFile);
        } catch (Exception $e) {
            writeLog("Fehler beim Verarbeiten der Nachricht: " . $e->getMessage(), 'ERROR', $batchLogFile);
            throw $e;
        }

        // Platzhalter ersetzen
        $subject = $placeholderService->replacePlaceholders($job['subject'], $placeholders);

        $message = $job['message'];
        $message = $placeholderService->replacePlaceholders($message, $placeholders);
        $message = makeUrlsAbsolute($message, $APP_URL);



        // Abmelde-Link hinzufügen
        $unsubscribeUrl = $APP_URL . "/modules/newsletter/unsubscribe.php?email=" .
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

            $db->commit();
            writeLog("E-Mail erfolgreich gesendet an: " . $job['recipient_email'], 'INFO', $batchLogFile);
        } else {
            throw new Exception($sendResult['error'] ?? 'Unbekannter Fehler');
        }
    } catch (Exception $e) {
        $db->rollback();
        throw $e; // Weitergabe an übergeordnete Fehlerbehandlung
    }
}

/**
 * Markiert einen Job als übersprungen
 */
function markJobSkipped($db, $jobId, $reason)
{
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("
            UPDATE email_jobs 
            SET status = 'skipped',
                error_message = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("si", $reason, $jobId);
        $stmt->execute();

        $stmt = $db->prepare("
            INSERT INTO email_logs 
            (job_id, status, response, created_at)
            VALUES (?, 'skipped', ?, NOW())
        ");
        $stmt->bind_param("is", $jobId, $reason);
        $stmt->execute();

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        writeLog("Fehler beim Markieren als übersprungen: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Markiert einen Job als fehlgeschlagen
 */
function markJobFailed($db, $jobId, $error)
{
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("
            UPDATE email_jobs 
            SET status = 'failed',
                error_message = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $errorMessageDb = substr($error, 0, 255);
        $stmt->bind_param("si", $errorMessageDb, $jobId);
        $stmt->execute();

        $stmt = $db->prepare("
            INSERT INTO email_logs 
            (job_id, status, response, created_at)
            VALUES (?, 'failed', ?, NOW())
        ");
        $stmt->bind_param("is", $jobId, $error);
        $stmt->execute();

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        writeLog("Fehler beim Markieren als fehlgeschlagen: " . $e->getMessage(), 'ERROR');
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

function makeUrlsAbsolute($content, $baseUrl)
{
    global $batchLogFile;
    $baseUrl = rtrim($baseUrl, '/');

    writeLog("Starte URL-Ersetzung mit Basis-URL: $baseUrl", 'INFO', $batchLogFile);

    $patterns = [
        ['pattern' => '/(src\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'src'],
        ['pattern' => '/(href\s*=\s*)"(\/users\/[^"]+)"/i', 'attr' => 'href'],
    ];

    foreach ($patterns as $p) {
        $content = preg_replace_callback(
            $p['pattern'],
            function ($matches) use ($baseUrl, $batchLogFile) {
                $oldUrl = $matches[2];
                $newUrl = $baseUrl . $oldUrl;
                writeLog("Ersetze URL: $oldUrl -> $newUrl", 'DEBUG', $batchLogFile);
                return $matches[1] . '"' . $newUrl . '"';
            },
            $content
        );
    }

    return $content;
}