<?php
/**
 * Korrigiert die verbleibenden LOG_PATH-Referenzen in process_batch.php
 */

$file = __DIR__ . '/exec/process_batch.php';
$content = file_get_contents($file);

if (!$content) {
    die("Fehler beim Lesen von $file");
}

// Backup erstellen
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup erstellt: $backup\n";

// LOG_PATH durch NEWSLETTER_LOG_PATH ersetzen
$pattern = '/if \(!is_dir\(LOG_PATH\)\) \{/';
$replacement = 'if (!is_dir(NEWSLETTER_LOG_PATH)) {';
$content = preg_replace($pattern, $replacement, $content);

// Prüfen ob ersetzt wurde
if (strpos($content, 'if (!is_dir(NEWSLETTER_LOG_PATH))') === false) {
    die("Fehler: Konnte LOG_PATH nicht ersetzen\n");
}

// Datei speichern
if (file_put_contents($file, $content)) {
    echo "Die letzte LOG_PATH-Referenz wurde durch NEWSLETTER_LOG_PATH ersetzt.\n";
} else {
    echo "Fehler beim Schreiben in $file\n";
}
?>
