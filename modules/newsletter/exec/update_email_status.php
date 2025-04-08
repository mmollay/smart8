<?php
/**
 * Aktualisiert E-Mail-Jobs, die bereits versendet wurden, aber noch "pending" Status haben
 */

// Fehlerberichterstattung aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Einbinden der notwendigen Dateien
require_once(__DIR__ . '/../n_config.php');

// Parameter auslesen
$options = getopt('', ['content-id::']);
$contentId = isset($options['content-id']) ? (int)$options['content-id'] : null;

// Funktion zum Schreiben von Log-Nachrichten
function writeLog($message) {
    echo date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
}

// Die Datenbankverbindung aus n_config.php verwenden
$db = $newsletterDb; // Diese Variable wurde in n_config.php definiert
if (!$db) {
    writeLog("Keine Datenbankverbindung verfügbar");
    exit(1);
}

writeLog("Datenbankverbindung hergestellt");

// SQL-Abfrage zur Identifikation von E-Mails, die aktualisiert werden müssen
$sql = "
    SELECT ej.id, ej.content_id, ej.recipient_id, r.email 
    FROM email_jobs ej
    JOIN recipients r ON ej.recipient_id = r.id
    WHERE ej.status = 'pending' AND ej.created_at <= NOW() - INTERVAL 2 MINUTE
";

// Füge content_id-Filter hinzu, wenn angegeben
if ($contentId) {
    $sql .= " AND ej.content_id = " . $contentId;
}

// Führe die Abfrage aus
$result = $db->query($sql);

if (!$result) {
    writeLog("Fehler bei der Abfrage: " . $db->error);
    exit(1);
}

$count = $result->num_rows;
writeLog("$count E-Mail-Jobs gefunden, die aktualisiert werden müssen");

if ($count == 0) {
    writeLog("Keine Jobs zum Aktualisieren gefunden");
    exit(0);
}

// Aktualisiere die Status und setze message_id
$updatedCount = 0;
$failed = 0;

while ($row = $result->fetch_assoc()) {
    $jobId = $row['id'];
    $email = $row['email'];
    
    // Generiere eine eindeutige message_id, falls keine vorhanden ist
    $messageId = "auto_" . md5($jobId . $email . time());
    
    // Status aktualisieren
    $updateSql = "
        UPDATE email_jobs 
        SET status = 'send', message_id = ?, sent_at = NOW() 
        WHERE id = ? AND status = 'pending'
    ";
    
    $stmt = $db->prepare($updateSql);
    $stmt->bind_param('si', $messageId, $jobId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $updatedCount++;
            writeLog("Job #$jobId für $email auf Status 'send' aktualisiert");
        } else {
            writeLog("Job #$jobId wurde nicht aktualisiert (kein pending-Status oder bereits aktualisiert)");
        }
    } else {
        $failed++;
        writeLog("Fehler beim Aktualisieren von Job #$jobId: " . $stmt->error);
    }
    
    $stmt->close();
}

// Update des Newsletter-Status, falls alle Jobs eines Newsletters aktualisiert wurden
if ($contentId) {
    // Prüfe, ob alle Jobs für den angegebenen Content-ID nun den Status 'send' haben
    $checkSql = "
        SELECT COUNT(*) as pending_count 
        FROM email_jobs 
        WHERE content_id = ? AND status = 'pending'
    ";
    
    $stmt = $db->prepare($checkSql);
    $stmt->bind_param('i', $contentId);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    $row = $checkResult->fetch_assoc();
    
    if ($row['pending_count'] == 0) {
        // Alle Jobs für diesen Newsletter wurden verarbeitet, also Update des Newsletter-Status
        $updateContentSql = "
            UPDATE email_contents 
            SET status = 'completed', completed_at = NOW() 
            WHERE id = ? AND status IN ('pending', 'running')
        ";
        
        $stmt = $db->prepare($updateContentSql);
        $stmt->bind_param('i', $contentId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            writeLog("Newsletter #$contentId Status auf 'completed' aktualisiert");
        }
    }
}

writeLog("Statusaktualisierung abgeschlossen: $updatedCount aktualisiert, $failed fehlgeschlagen");

// Schließe die Datenbankverbindung
$db->close();
writeLog("Datenbankverbindung geschlossen");
?>
