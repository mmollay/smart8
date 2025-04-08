<?php
// Lese die Datei ein
$file = __DIR__ . '/exec/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die Variablennutzung an der richtigen Stelle im Code
$pattern = '/\$processId = getmypid\(\);

\/\/ Logdatei für diesen Batch.*/s';
$replacement = '$processId = getmypid();

// Hole die ContentID und JobIDs vom Kommandozeilenaufruf
$options = getopt("", ["content-id:", "job-ids:"]);
$contentId = (int) $options["content-id"];
$jobIds = explode(",", $options["job-ids"]);

// Logdatei für diesen Batch
$batchLogFile = NEWSLETTER_LOG_PATH . \'/newsletter_batch_\' . $contentId . \'_\' . date(\'Ymd-His\') . \'.log\';
writeLog("Starte Batch-Verarbeitung mit Content ID $contentId und Jobs: " . implode(\',\', $jobIds), \'INFO\', true);
writeLog("Batch-Log wird in $batchLogFile gespeichert", \'INFO\');';

$content = preg_replace($pattern, $replacement, $content);

// Entferne den doppelten Code für die Kommandozeilenparameter
$pattern2 = '/\/\/ Kommandozeilenargumente verarbeiten.*die\("Erforderliche Parameter fehlen\\\\n"\);.*?\$processId = getmypid\(\);/s';
$replacement2 = '// Prozess-ID setzen
$processId = getmypid();';

$content = preg_replace($pattern2, $replacement2, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die Variablen korrekt zu verwenden.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
