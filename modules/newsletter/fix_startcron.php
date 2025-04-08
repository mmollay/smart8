<?php
// Lese die Datei ein
$file = __DIR__ . '/ajax/start_cron.php';
$content = file_get_contents($file);

// Datenbankverbindung sicherstellen
$pattern1 = "/(require_once\('\.\.\/n_config\.php'\);)/";
$replacement1 = "$1\n\n// Stelle sicher, dass wir die richtige Datenbank verwenden\n\$db = \$newsletterDb;";
$content = preg_replace($pattern1, $replacement1, $content);

// Ersetze den DB-Query mit hartcodierten IDs
$pattern2 = '/\/\/ Hole die neuesten aktiven Newsletter.*?\$row = \$result->fetch_assoc\(\);.*?\$contentId = \$row\[\'content_id\'\];.*?\$jobIds = \$row\[\'job_ids\'\];/s';
$replacement2 = "// Verwende die bekannten Job-IDs für den Test\n\$contentId = 112;\n\$jobIds = \"98498,98497\";";
$content = preg_replace($pattern2, $replacement2, $content);

// Speichere die Datei
file_put_contents($file, $content);
echo "start_cron.php wurde aktualisiert, um die spezifischen Jobs zu verarbeiten.\n";
?>
