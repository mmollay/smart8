<?php
/**
 * Verarbeitet E-Mail-Batches für den Newsletter-Versand
 * 
 * Dieses Skript verarbeitet E-Mail-Jobs für einen bestimmten Newsletter
 * und sendet die E-Mails an die Empfänger.
 * 
 * Verwendung:
 * php process_batch.php --content-id=123 --job-ids=1,2,3,4
 */

// Fehlerberichterstattung aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Einbinden der notwendigen Dateien
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/BrevoEmailService.php');
require_once(__DIR__ . '/../classes/PlaceholderService.php');

// Konsolen-Parameter auslesen
$options = getopt('', ['content-id:', 'job-ids:']);

if (!isset($options['content-id']) || !isset($options['job-ids'])) {
    echo "Verwendung: php process_batch.php --content-id=123 --job-ids=1,2,3,4\n";
    exit(1);
}

$contentId = (int)$options['content-id'];
$jobIds = array_map('intval', explode(',', $options['job-ids']));

// Log-Datei initialisieren
$batchLogFile = __DIR__ . '/../logs/batch_' . date('Ymd_His') . '_' . $contentId . '.log';

// Stelle sicher, dass das Verzeichnis existiert
if (!is_dir(dirname($batchLogFile))) {
    mkdir(dirname($batchLogFile), 0777, true);
}

// Hilfsfunktion für Logging
function writeLog($message, $level = 'INFO', $echo = false) {
    global $batchLogFile;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // In Log-Datei schreiben
    file_put_contents($batchLogFile, $logMessage, FILE_APPEND);
    
    // Optional auch auf der Konsole ausgeben
    if ($echo) {
        echo $logMessage;
    }
}

// Beginn der Verarbeitung
writeLog("Starte Batch-Verarbeitung mit Content ID $contentId und Jobs: " . implode(',', $jobIds), 'INFO', true);

// Die Datenbankverbindung aus n_config.php verwenden
$db = $newsletterDb; // Diese Variable wurde in n_config.php definiert
if (!$db) {
    writeLog("Keine Datenbankverbindung verfügbar", 'ERROR', true);
    exit(1);
}
writeLog("Bestehende Datenbankverbindung verwendet", 'INFO');

// Services initialisieren
$emailService = new BrevoEmailService($db, $_ENV['BREVO_API_KEY'], $uploadBasePath);
$placeholderService = PlaceholderService::getInstance();

// Newsletter-Informationen laden
try {
    $stmt = $db->prepare("
        SELECT 
            n.id, 
            n.subject, 
            n.html_content AS content, 
            CONCAT(s.first_name, ' ', s.last_name) AS sender_name, 
            s.email AS sender_email, 
            s.email AS reply_to_email,
            CONCAT(s.first_name, ' ', s.last_name) AS reply_to_name, 
            1 AS html_format
        FROM email_contents n
        JOIN senders s ON n.sender_id = s.id
        WHERE n.id = ?
    ");
    $stmt->bind_param('i', $contentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        writeLog("Newsletter mit ID $contentId nicht gefunden", 'ERROR', true);
        exit(1);
    }
    
    $newsletter = $result->fetch_assoc();
    writeLog("Newsletter '{$newsletter['subject']}' geladen", 'INFO');
} catch (Exception $e) {
    writeLog("Fehler beim Laden des Newsletters: " . $e->getMessage(), 'ERROR', true);
    exit(1);
}

// Jobs verarbeiten
$totalJobs = count($jobIds);
$successCount = 0;
$errorCount = 0;

writeLog("Beginne die Verarbeitung von $totalJobs Jobs", 'INFO', true);

foreach ($jobIds as $jobId) {
    writeLog("Verarbeite Job $jobId", 'INFO');
    $result = processJob($db, $emailService, $placeholderService, $contentId, $jobId, $newsletter);
    
    if ($result['success']) {
        $successCount++;
        writeLog("Job $jobId erfolgreich verarbeitet: " . $result['message'], 'INFO');
    } else {
        $errorCount++;
        writeLog("Fehler bei Job $jobId: " . $result['message'], 'ERROR');
    }
}

// Zusammenfassung ausgeben
writeLog("Batch-Verarbeitung abgeschlossen: $successCount erfolgreich, $errorCount fehlgeschlagen von $totalJobs gesamt", 'INFO', true);

/**
 * Verarbeitet einen einzelnen E-Mail-Job
 * 
 * @param mysqli $db Datenbankverbindung
 * @param BrevoEmailService $emailService E-Mail-Service
 * @param PlaceholderService $placeholderService Platzhalter-Service
 * @param int $contentId ID des Newsletters
 * @param int $jobId ID des Jobs
 * @param array|null $newsletter Newsletter-Daten (optional)
 * @return array Ergebnis mit success und message
 */
function processJob($db, $emailService, $placeholderService, $contentId, $jobId, $newsletter = null) {
    try {
        // Job-Details laden
        $stmt = $db->prepare("
            SELECT j.id, j.recipient_id, r.email, r.first_name, r.last_name, 
                   r.title, r.gender, r.company
            FROM email_jobs j 
            JOIN recipients r ON j.recipient_id = r.id
            WHERE j.id = ? AND j.content_id = ?
        ");
        $stmt->bind_param('ii', $jobId, $contentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Job $jobId für Newsletter $contentId nicht gefunden");
        }
        
        $job = $result->fetch_assoc();
        
        // Newsletter-Details laden, falls nicht übergeben
        if (!$newsletter) {
            $stmt = $db->prepare("
                SELECT 
                    n.id, 
                    n.subject, 
                    n.html_content AS content, 
                    CONCAT(s.first_name, ' ', s.last_name) AS sender_name, 
                    s.email AS sender_email, 
                    s.email AS reply_to_email,
                    CONCAT(s.first_name, ' ', s.last_name) AS reply_to_name, 
                    1 AS html_format
                FROM email_contents n
                JOIN senders s ON n.sender_id = s.id
                WHERE n.id = ?
            ");
            $stmt->bind_param('i', $contentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Newsletter mit ID $contentId nicht gefunden");
            }
            
            $newsletter = $result->fetch_assoc();
        }
        
        // Benutzerdefinierte Felder verarbeiten
        $customFields = [];
        // Keine benutzerdefinierten Felder vorhanden
        $customFields = [];
        
        // Empfänger-Informationen vorbereiten
        $recipient = [
            'email' => $job['email'],
            'firstname' => $job['firstname'] ?? '',
            'lastname' => $job['lastname'] ?? '',
            'title' => $job['title'] ?? '',
            'gender' => $job['gender'] ?? '',
            'company' => $job['company'] ?? ''
        ];
        
        // Platzhalter im Betreff und Inhalt ersetzen
        // Sicherstellen, dass subject nicht null ist
        $subject = $newsletter['subject'] ?? 'Keine Betreffzeile';
        $subject = $placeholderService->replacePlaceholders($subject, $recipient, $customFields);
        // Sicherstellen, dass content nicht null ist
        $content = $newsletter['content'] ?? 'Keine Inhalte';
        $content = $placeholderService->replacePlaceholders($content, $recipient, $customFields);
        
        // Sender-Informationen vorbereiten
        $sender = [
            'name' => $newsletter['sender_name'],
            'email' => $newsletter['sender_email'],
            'reply_name' => $newsletter['reply_to_name'] ?? $newsletter['sender_name'],
            'reply_email' => $newsletter['reply_to_email'] ?? $newsletter['sender_email']
        ];
        
        // E-Mail senden
        $result = $emailService->sendSingleEmail(
            $contentId,
            $sender,
            $recipient,
            $subject,
            $content,
            $jobId,
            false
        );
        
        $success = $result['success'] ?? false;
        $messageId = $result['message_id'] ?? '';
        $resultMessage = $result['message'] ?? '';
        
        // Status des Jobs in der Datenbank aktualisieren
        $stmt = $db->prepare("
            UPDATE email_jobs 
            SET status = ?, message_id = ?, sent_at = NOW() 
            WHERE id = ?
        ");
        $status = 'send';
        $stmt->bind_param('ssi', $status, $messageId, $jobId);
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => "E-Mail gesendet an {$recipient['email']} mit Message-ID: $messageId. $resultMessage"
        ];
    } catch (Exception $e) {
        // Fehler protokollieren und Status in der Datenbank aktualisieren
        $errorMessage = $resultMessage ?: $e->getMessage();
        
        $stmt = $db->prepare("
            UPDATE email_jobs 
            SET status = ?, error_message = ?, sent_at = NOW() 
            WHERE id = ?
        ");
        $status = 'failed';
        $stmt->bind_param('ssi', $status, $errorMessage, $jobId);
        $stmt->execute();
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }
}