<?php
/**
 * Entfernt dreifache 'send' Cases in der newsletters.php
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

// Extrahiere die Zeilen mit dem 'send' Case
preg_match_all('/(case \'send\'.*?)(?=case|$)/s', $content, $matches);

if (count($matches[0]) > 1) {
    echo "Es wurden " . count($matches[0]) . " 'send' Cases gefunden. Behalte nur den ersten.\n";
    
    // Der erste 'send' Case
    $firstSendCase = $matches[0][0];
    
    // Lösche alle 'send' Cases
    $pattern = '/case \'send\'.*?(?=case|$)/s';
    $content = preg_replace($pattern, '', $content);
    
    // Füge den ersten 'send' Case nach 'running' wieder ein
    $runningPattern = '/(case \'running\'.*?)(?=case|$)/s';
    $replacement = "$1\n                " . $firstSendCase;
    $content = preg_replace($runningPattern, $replacement, $content);
    
    // Speichere die Datei
    if (file_put_contents($file, $content)) {
        echo "newsletters.php wurde aktualisiert. Die doppelten 'send' Cases wurden entfernt.\n";
    } else {
        echo "Fehler beim Aktualisieren von newsletters.php\n";
    }
} else {
    echo "Weniger als 2 'send' Cases gefunden. Keine Änderung notwendig.\n";
}

// Erfolgsmeldung
echo "Korrektur abgeschlossen. Bitte die Newsletter-Seite neu laden.\n";
?>
