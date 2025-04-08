<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Ersetze alle Vorkommen von processed_at durch sent_at
$content = str_replace('processed_at', 'sent_at', $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, alle 'processed_at' wurden durch 'sent_at' ersetzt.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
