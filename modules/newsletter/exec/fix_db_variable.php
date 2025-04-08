<?php
/**
 * Korrigiert die fehlende $db Variable in der Formatter-Funktion
 */

// Fehlerberichterstattung aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Lese die Datei ein
$file = __DIR__ . '/../lists/newsletters.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von newsletters.php erstellt: $backup\n";

// Suche die Formatter-Funktion und füge die $db-Initialisierung am Anfang ein
$pattern = "/'formatter' => function \(\\\$value, \\\$row\) {/";
$replacement = "'formatter' => function (\$value, \$row) {
            // Initialisiere die Datenbankverbindung
            global \$newsletterDb;
            \$db = \$newsletterDb;
            
            if (!\$db) {
                require_once __DIR__ . '/../n_config.php';
                \$db = \$newsletterDb;
            }";

// Wende die Ersetzung an
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde aktualisiert. Die \$db Variable wurde initialisiert.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}

// Erfolgsmeldung
echo "Korrektur abgeschlossen. Bitte die Newsletter-Seite neu laden.\n";
?>
