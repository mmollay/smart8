<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die PlaceholderService-Instanziierung
$pattern = "/\\\$placeholderService = new PlaceholderService\\(\\);/";
$replacement = "\$placeholderService = PlaceholderService::getInstance();";

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um den PlaceholderService korrekt zu verwenden.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
