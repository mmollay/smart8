<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die SQL-Abfragen mit den richtigen Spaltennamen
$sqlPatterns = [
    // Erste Abfrage für Newsletter-Informationen
    "/SELECT n\\.id, n\\.subject, n\\.content, n\\.sender_name, n\\.sender_email, n\\.reply_to_email, 
               n\\.reply_to_name, n\\.html_format/s" => 
    "SELECT n.id, n.subject, n.html_content AS content, s.name AS sender_name, 
               s.email AS sender_email, s.reply_email AS reply_to_email,
               s.reply_name AS reply_to_name, 1 AS html_format
        FROM email_contents n
        JOIN senders s ON n.sender_id = s.id",
    
    // Zweite Abfrage in der processJob-Funktion für Newsletter-Informationen
    "/SELECT n\\.id, n\\.subject, n\\.content, n\\.sender_name, n\\.sender_email, n\\.reply_to_email, 
                       n\\.reply_to_name, n\\.html_format
                FROM email_contents n
                WHERE n\\.id = \\?/s" => 
    "SELECT n.id, n.subject, n.html_content AS content, s.name AS sender_name, 
                       s.email AS sender_email, s.reply_email AS reply_to_email,
                       s.reply_name AS reply_to_name, 1 AS html_format
                FROM email_contents n
                JOIN senders s ON n.sender_id = s.id
                WHERE n.id = ?"
];

foreach ($sqlPatterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

// Korrigiere die SELECT-Abfrage in der processJob-Funktion für Empfänger-Informationen
$recipientPattern = "/SELECT j\\.id, j\\.recipient_id, j\\.custom_fields, r\\.email, r\\.firstname, r\\.lastname, 
                   r\\.title, r\\.gender, r\\.company
            FROM email_jobs j 
            JOIN recipients r ON j\\.recipient_id = r\\.id
            WHERE j\\.id = \\? AND j\\.content_id = \\?/s";

$recipientReplacement = "SELECT j.id, j.recipient_id, j.custom_fields, r.email, r.firstname, r.lastname, 
                   r.title, r.gender, r.company
            FROM email_jobs j 
            JOIN recipients r ON j.recipient_id = r.id
            WHERE j.id = ? AND j.content_id = ?";

$content = preg_replace($recipientPattern, $recipientReplacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die korrekten Spaltennamen zu verwenden.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
