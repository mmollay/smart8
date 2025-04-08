<?php
// Lese die Datei ein
$file = __DIR__ . '/../lists/newsletters.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von newsletters.php erstellt: $backup\n";

// Finde und ersetze die beschädigte getJobStats-Funktion
$pattern = '/function getJobStats\s+global \$db;/';
$replacement = 'function getJobStats($contentId) {
    global $db;';

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde korrigiert. Die getJobStats-Funktion wurde repariert.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}
?>
