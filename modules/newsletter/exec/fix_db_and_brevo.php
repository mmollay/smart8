<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die Datenbankverbindung
$pattern = "/\\/\\/ Datenbankverbindung herstellen.*?try \\{.*?\\\$db = new mysqli\\(\\\$newsletterDbHost, \\\$newsletterDbUser, \\\$newsletterDbPass, \\\$newsletterDbName\\);.*?\\}/s";
$replacement = "// Datenbankverbindung herstellen
try {
    // Die Datenbankverbindung aus n_config.php verwenden
    \$db = \$newsletterDb; // Diese Variable wurde in n_config.php definiert
    if (!\$db) {
        writeLog(\"Keine Datenbankverbindung verfügbar\", 'ERROR', true);
        exit(1);
    }
    writeLog(\"Bestehende Datenbankverbindung verwendet\", 'INFO');";

$content = preg_replace($pattern, $replacement, $content);

// Korrigiere die Initialisierung von BrevoEmailService
$pattern = "/\\\$emailService = new BrevoEmailService\\(\\\$db\\);/";
$replacement = "\$emailService = new BrevoEmailService(\$db, \$_ENV['BREVO_API_KEY'], \$uploadBasePath);";

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert mit den korrekten Datenbankparametern und BrevoEmailService-Initialisierung.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
