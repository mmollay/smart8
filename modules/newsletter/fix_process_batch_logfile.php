<?php
// Lese die Datei ein
$file = __DIR__ . '/exec/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Definiere die $batchLogFile-Variable am Anfang des Skripts
$pattern = '/\$processId = getmypid\(\);/';
$replacement = '$processId = getmypid();

// Logdatei für diesen Batch
$batchLogFile = NEWSLETTER_LOG_PATH . \'/newsletter_batch_\' . $contentId . \'_\' . date(\'Ymd-His\') . \'.log\';
writeLog("Starte Batch-Verarbeitung mit Content ID $contentId und Jobs: " . implode(\',\', $jobIds), \'INFO\', true);
writeLog("Batch-Log wird in $batchLogFile gespeichert", \'INFO\');';

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die fehlende \$batchLogFile-Variable hinzuzufügen.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
