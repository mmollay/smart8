<?php
/**
 * Dieses Skript korrigiert die start_cron.php, um direkt process_batch.php aufzurufen
 */

$file = __DIR__ . '/ajax/start_cron.php';
$content = file_get_contents($file);

if (!$content) {
    die("Fehler beim Lesen von $file");
}

// Backup erstellen
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup erstellt: $backup\n";

// Korrigiere den Befehl
$pattern = '/\$command = \'\';.*?if \(\$_SERVER\[\'SERVER_NAME\'\] === \'localhost\'\) \{.*?\$command = ".*?";.*?\} else \{.*?\$command = ".*?";.*?\}/s';
$replacement = <<<EOT
\$command = '';
if (\$_SERVER['SERVER_NAME'] === 'localhost') {
    \$command = "export PATH=/Applications/XAMPP/xamppfiles/bin:/usr/local/bin:/usr/bin:/bin && cd " . __DIR__ . "/../exec && php process_batch.php 2>&1";
} else {
    \$command = "cd " . __DIR__ . "/../exec && /usr/bin/php process_batch.php 2>&1";
}
EOT;

$newContent = preg_replace($pattern, $replacement, $content);

if (file_put_contents($file, $newContent)) {
    echo "Datei $file erfolgreich aktualisiert!\n";
    echo "Jetzt wird direkt process_batch.php aufgerufen statt cron_controller.php\n";
} else {
    echo "Fehler beim Schreiben in $file\n";
}
?>
