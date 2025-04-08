<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Füge den restlichen Funktionscode nach dem letzten writeLog hinzu
$additionalCode = <<<'EOD'

// Datenbankverbindung herstellen
try {
    $db = new mysqli($newsletterDbHost, $newsletterDbUser, $newsletterDbPass, $newsletterDbName);
    if ($db->connect_errno) {
        writeLog("Fehler bei der Datenbankverbindung: " . $db->connect_error, 'ERROR', true);
        exit(1);
    }
    $db->set_charset('utf8mb4');
    writeLog("Datenbankverbindung hergestellt", 'INFO');
} catch (Exception $e) {
    writeLog("Fehler bei der Datenbankverbindung: " . $e->getMessage(), 'ERROR', true);
    exit(1);
}

// Services initialisieren
try {
    $emailService = new BrevoEmailService($db);
    $placeholderService = new PlaceholderService();
    writeLog("Dienste initialisiert", 'INFO');
} catch (Exception $e) {
    writeLog("Fehler bei der Initialisierung der Dienste: " . $e->getMessage(), 'ERROR', true);
    exit(1);
}

// Newsletter-Informationen laden
try {
    $stmt = $db->prepare("
        SELECT n.id, n.subject, n.content, n.sender_name, n.sender_email, n.reply_to_email, 
               n.reply_to_name, n.html_format
        FROM newsletter_content n
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
    } else {
        $errorCount++;
        writeLog("Fehler bei Job $jobId: " . $result['message'], 'ERROR');
    }
}

// Abschluss
writeLog("Batch-Verarbeitung abgeschlossen", 'INFO', true);
writeLog("Erfolgreich: $successCount, Fehler: $errorCount, Gesamt: $totalJobs", 'INFO', true);

// Datenbankverbindung schließen
$db->close();
writeLog("Datenbankverbindung geschlossen", 'INFO');

exit($errorCount > 0 ? 1 : 0);

/**
 * Verarbeitet einen einzelnen Email-Job
 */
function processJob($db, $emailService, $placeholderService, $contentId, $jobId, $newsletter = null) {
    global $batchLogFile;
    
    try {
        // Status auf 'processing' setzen
        $stmt = $db->prepare("UPDATE email_jobs SET status = 'processing', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        
        // Job-Details laden
        $stmt = $db->prepare("
            SELECT j.id, j.recipient_id, j.custom_fields, r.email, r.firstname, r.lastname, 
                   r.title, r.gender, r.company
            FROM email_jobs j 
            JOIN newsletter_recipients r ON j.recipient_id = r.id
            WHERE j.id = ? AND j.content_id = ?
        ");
        $stmt->bind_param('ii', $jobId, $contentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Job $jobId oder Empfänger nicht gefunden");
        }
        
        $job = $result->fetch_assoc();
        writeLog("Job $jobId: Versende an {$job['email']}", 'INFO');
        
        // Newsletter-Details laden, falls nicht übergeben
        if (!$newsletter) {
            $stmt = $db->prepare("
                SELECT n.id, n.subject, n.content, n.sender_name, n.sender_email, n.reply_to_email, 
                       n.reply_to_name, n.html_format
                FROM newsletter_content n
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
        
        // Platzhalter vorbereiten
        $customFields = json_decode($job['custom_fields'], true) ?: [];
        $placeholders = [
            'vorname' => $job['firstname'] ?: 'Nutzer',
            'nachname' => $job['lastname'] ?: '',
            'titel' => $job['title'] ?: '',
            'geschlecht' => $job['gender'] ?: '',
            'firma' => $job['company'] ?: '',
            'company' => $job['company'] ?: '',
            'email' => $job['email']
        ];
        
        // Anredetext basierend auf Geschlecht
        if ($job['gender'] === 'Herr') {
            $anrede = 'Sehr geehrter Herr';
        } elseif ($job['gender'] === 'Frau') {
            $anrede = 'Sehr geehrte Frau';
        } else {
            $anrede = 'Sehr geehrte*r';
        }
        
        if (!empty($job['title'])) {
            $anrede .= ' ' . $job['title'];
        }
        
        $anrede .= ' ' . $job['lastname'];
        $placeholders['anrede'] = $anrede;
        
        // Custom-Felder hinzufügen
        $placeholders = array_merge($placeholders, $customFields);
        
        // Platzhalter ersetzen
        $htmlContent = $newsletter['content'];
        $subject = $newsletter['subject'];
        
        foreach ($placeholders as $key => $value) {
            $htmlContent = str_replace('{{'.$key.'}}', $value, $htmlContent);
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
        }
        
        // E-Mail versenden
        $sendResult = $emailService->sendSingleEmail(
            $newsletter['id'],
            [
                'name' => $newsletter['sender_name'],
                'email' => $newsletter['sender_email']
            ],
            [
                'name' => $job['firstname'] . ' ' . $job['lastname'],
                'email' => $job['email']
            ],
            $subject,
            $htmlContent,
            $jobId
        );
        
        if ($sendResult['success']) {
            // E-Mail erfolgreich versendet - Status auf 'sent' setzen
            $stmt = $db->prepare("UPDATE email_jobs SET status = 'sent', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('i', $jobId);
            $stmt->execute();
            
            writeLog("Job $jobId: E-Mail erfolgreich versendet. Message-ID: " . $sendResult['message_id'], 'INFO');
            return ['success' => true, 'message' => 'E-Mail versendet'];
        } else {
            throw new Exception("Fehler beim Versenden: " . ($sendResult['message'] ?? 'Unbekannter Fehler'));
        }
    } catch (Exception $e) {
        // Bei Fehler Status auf 'error' setzen
        $stmt = $db->prepare("UPDATE email_jobs SET status = 'error', error_message = ?, updated_at = NOW() WHERE id = ?");
        $errorMsg = substr($e->getMessage(), 0, 255);
        $stmt->bind_param('si', $errorMsg, $jobId);
        $stmt->execute();
        
        writeLog("Job $jobId: Fehler: " . $e->getMessage(), 'ERROR');
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
EOD;

// Füge den neuen Code zur Datei hinzu
$newContent = $content . "\n" . $additionalCode;

// Speichere die Datei
if (file_put_contents($file, $newContent)) {
    echo "process_batch.php wurde mit der vollständigen E-Mail-Verarbeitungslogik aktualisiert.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
