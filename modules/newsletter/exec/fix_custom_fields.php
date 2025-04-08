<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Entferne custom_fields aus allen SQL-Abfragen
$pattern = "/j\.recipient_id, j\.custom_fields, r\.email/";
$replacement = "j.recipient_id, r.email";
$content = preg_replace($pattern, $replacement, $content);

// Entferne custom_fields aus der Verarbeitung im PHP-Code
$pattern = "/if \(!empty\(\\\$job\['custom_fields'\]\)\) \{.*?\\\$customFields = json_decode\(\\\$job\['custom_fields'\], true\) \?: \[\];.*?\}/s";
$replacement = "// Keine benutzerdefinierten Felder vorhanden
        \$customFields = [];";
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die nicht vorhandene Spalte 'custom_fields' zu entfernen.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
