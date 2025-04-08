<?php
/**
 * Dieses Skript korrigiert Fehler in process_batch.php
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

// 1. Fehlende Klammer nach der Klassenüberprüfung hinzufügen
$pattern1 = '/if \(!class_exists\(\'BrevoEmailService\'\) \|\| !class_exists\(\'PlaceholderService\'\)\) \{(\s+?)\/\/ Kommandozeilenargumente/s';
$replacement1 = "if (!class_exists('BrevoEmailService') || !class_exists('PlaceholderService')) {\n    die(\"Erforderliche Klassen nicht gefunden\\n\");\n}\n\n// Kommandozeilenargumente";

$content = preg_replace($pattern1, $replacement1, $content);

// 2. LOG_PATH durch NEWSLETTER_LOG_PATH ersetzen
$pattern2 = '/\$logFile = LOG_PATH \. \'\/cron_controller\.log\';/';
$replacement2 = '$logFile = NEWSLETTER_LOG_PATH . \'/newsletter_batch.log\';';

$content = preg_replace($pattern2, $replacement2, $content);

// 3. LOG_PATH-Definition entfernen (wir nutzen jetzt NEWSLETTER_LOG_PATH)
$pattern3 = '/\/\/ Am Anfang der Datei nach den defines\s*define\(\'LOG_PATH\', \'\/var\/www\/ssi\/smart8\/logs\'\);/';
$replacement3 = '// Wir verwenden NEWSLETTER_LOG_PATH statt LOG_PATH';

$content = preg_replace($pattern3, $replacement3, $content);

// Logverzeichnis-Prüfung in writeLog hinzufügen
$pattern4 = '/\$logMessage \.\= "\\n";\s*\$logFile = NEWSLETTER_LOG_PATH \. \'\\/newsletter_batch\.log\';/';
$replacement4 = '$logMessage .= "\n";
    $logFile = NEWSLETTER_LOG_PATH . \'/newsletter_batch.log\';
    
    // Erstelle Log-Verzeichnis falls es nicht existiert
    if (!is_dir(NEWSLETTER_LOG_PATH)) {
        mkdir(NEWSLETTER_LOG_PATH, 0755, true);
    }';

$content = preg_replace($pattern4, $replacement4, $content);

if (file_put_contents($file, $content)) {
    echo "Datei $file erfolgreich aktualisiert!\n";
    echo "Fehler wurden behoben:\n";
    echo "1. Fehlende Klammer nach der Klassenüberprüfung hinzugefügt\n";
    echo "2. LOG_PATH durch NEWSLETTER_LOG_PATH ersetzt\n";
    echo "3. Logverzeichnis-Prüfung in writeLog hinzugefügt\n";
} else {
    echo "Fehler beim Schreiben in $file\n";
}
?>
