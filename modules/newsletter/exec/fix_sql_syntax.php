<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Manuelles Korrigieren der SQL-Abfragen
// Wir ersetzen die gesamten SQL-Abfragen direkt im Code

// Finde die Stelle der ersten SQL-Abfrage nach "Newsletter-Informationen laden"
$newsletterQueryPos = strpos($content, "// Newsletter-Informationen laden");
if ($newsletterQueryPos !== false) {
    // Extrahiere einen Block von 500 Zeichen nach dieser Position
    $blockToReplace = substr($content, $newsletterQueryPos, 500);
    
    // Erstelle den Ersatz-Block mit der korrekten SQL-Abfrage
    $replacement = "// Newsletter-Informationen laden
try {
    \$stmt = \$db->prepare(\"
        SELECT 
            n.id, 
            n.subject, 
            n.html_content AS content, 
            s.name AS sender_name, 
            s.email AS sender_email, 
            s.reply_email AS reply_to_email,
            s.reply_name AS reply_to_name, 
            1 AS html_format
        FROM email_contents n
        JOIN senders s ON n.sender_id = s.id
        WHERE n.id = ?
    \");
    \$stmt->bind_param('i', \$contentId);
    \$stmt->execute();
    \$result = \$stmt->get_result();
    
    if (\$result->num_rows === 0) {
        writeLog(\"Newsletter mit ID \$contentId nicht gefunden\", 'ERROR', true);
        exit(1);
    }
    
    \$newsletter = \$result->fetch_assoc();
    writeLog(\"Newsletter '{\$newsletter['subject']}' geladen\", 'INFO');
} catch (Exception \$e) {
    writeLog(\"Fehler beim Laden des Newsletters: \" . \$e->getMessage(), 'ERROR', true);
    exit(1);
}";
    
    // Ersetze den Block im Inhalt
    $content = str_replace($blockToReplace, $replacement, $content);
}

// Finde die Stelle der zweiten SQL-Abfrage in der processJob-Funktion
$jobQueryPos = strpos($content, "// Job-Details laden");
if ($jobQueryPos !== false) {
    // Extrahiere einen Block von 400 Zeichen nach dieser Position
    $blockToReplace = substr($content, $jobQueryPos, 400);
    
    // Erstelle den Ersatz-Block mit der korrekten SQL-Abfrage
    $replacement = "// Job-Details laden
        \$stmt = \$db->prepare(\"
            SELECT j.id, j.recipient_id, j.custom_fields, r.email, r.firstname, r.lastname, 
                   r.title, r.gender, r.company
            FROM email_jobs j 
            JOIN recipients r ON j.recipient_id = r.id
            WHERE j.id = ? AND j.content_id = ?
        \");
        \$stmt->bind_param('ii', \$jobId, \$contentId);
        \$stmt->execute();
        \$result = \$stmt->get_result();";
    
    // Ersetze den Block im Inhalt
    $content = str_replace($blockToReplace, $replacement, $content);
}

// Finde die Stelle der dritten SQL-Abfrage in der processJob-Funktion (Newsletter-Details)
$newsletterDetailsPos = strpos($content, "// Newsletter-Details laden, falls nicht übergeben");
if ($newsletterDetailsPos !== false) {
    // Extrahiere einen Block von 500 Zeichen nach dieser Position
    $blockToReplace = substr($content, $newsletterDetailsPos, 500);
    
    // Erstelle den Ersatz-Block mit der korrekten SQL-Abfrage
    $replacement = "// Newsletter-Details laden, falls nicht übergeben
        if (!\$newsletter) {
            \$stmt = \$db->prepare(\"
                SELECT 
                    n.id, 
                    n.subject, 
                    n.html_content AS content, 
                    s.name AS sender_name, 
                    s.email AS sender_email, 
                    s.reply_email AS reply_to_email,
                    s.reply_name AS reply_to_name, 
                    1 AS html_format
                FROM email_contents n
                JOIN senders s ON n.sender_id = s.id
                WHERE n.id = ?
            \");
            \$stmt->bind_param('i', \$contentId);
            \$stmt->execute();
            \$result = \$stmt->get_result();
            
            if (\$result->num_rows === 0) {
                throw new Exception(\"Newsletter mit ID \$contentId nicht gefunden\");
            }
            
            \$newsletter = \$result->fetch_assoc();
        }";
    
    // Ersetze den Block im Inhalt
    $content = str_replace($blockToReplace, $replacement, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die SQL-Syntaxfehler zu beheben.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
