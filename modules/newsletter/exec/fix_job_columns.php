<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Ersetze die SQL-Abfragen mit den korrekten Spaltennamen
$jobsUpdatePattern = [
    "SET status = ?, message_id = ?, processed_at = NOW()" => "SET status = ?, message_id = ?, sent_at = NOW()"
];

foreach ($jobsUpdatePattern as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert mit den korrekten Spaltennamen für die email_jobs-Tabelle.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
