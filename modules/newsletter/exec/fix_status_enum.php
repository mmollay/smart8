<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Ersetze 'sent' durch 'send' (der korrekte Enum-Wert in der Datenbank)
$content = str_replace('$status = \'sent\';', '$status = \'send\';', $content);

// Ersetze auch 'error' durch 'failed' (der korrekte Enum-Wert in der Datenbank)
$content = str_replace('$status = \'error\';', '$status = \'failed\';', $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert mit den korrekten Status-Werten für die email_jobs-Tabelle.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
