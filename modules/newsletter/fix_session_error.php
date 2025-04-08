<?php
/**
 * Behebt den Session-Fehler in n_config.php für CLI-Ausführung
 */

$file = __DIR__ . '/n_config.php';
$content = file_get_contents($file);

if (!$content) {
    die("Fehler beim Lesen von $file");
}

// Backup erstellen
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup erstellt: $backup\n";

// Füge Prüfung für CLI-Modus ein
$pattern = '/\/\/upload path für Attachements der Emails\s*\$uploadBasePath = \$_ENV\[\'UPLOAD_PATH\'\] \. \'\/' . \$_SESSION\[\'user_id\'\] \. \'\/newsletters\';/';
$replacement = "//upload path für Attachements der Emails 
// Im CLI-Modus verwenden wir einen Standard-Pfad
if (\$isCliMode) {
    \$uploadBasePath = \$_ENV['UPLOAD_PATH'] . '/cli/newsletters';
} else {
    \$uploadBasePath = \$_ENV['UPLOAD_PATH'] . '/' . \$_SESSION['user_id'] . '/newsletters';
}";

$content = preg_replace($pattern, $replacement, $content);

// Datei speichern
if (file_put_contents($file, $content)) {
    echo "n_config.php wurde aktualisiert, um im CLI-Modus ohne SESSION zu funktionieren.\n";
} else {
    echo "Fehler beim Schreiben in $file\n";
}
?>
