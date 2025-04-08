<?php
/**
 * Entfernt doppelte 'send' Cases in der newsletters.php
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

// Lösche doppelte 'send' Cases
if (preg_match_all('/case \'send\':/s', $content, $matches) > 1) {
    echo "Doppelter 'send' Case gefunden! Entferne den zweiten Fall...\n";
    
    // Finde die Position des ersten 'send' Case
    $firstPos = strpos($content, "case 'send':");
    
    // Finde die Position des zweiten 'send' Case
    $secondPos = strpos($content, "case 'send':", $firstPos + 1);
    
    if ($firstPos !== false && $secondPos !== false) {
        // Finde das Ende des ersten 'send' Cases (bis zum nächsten case oder Ende)
        $nextCasePos = strpos($content, "case '", $firstPos + 12);
        if ($nextCasePos === $secondPos) {
            // Der zweite 'send' Case folgt direkt auf den ersten
            $nextCaseAfterSecond = strpos($content, "case '", $secondPos + 12);
            if ($nextCaseAfterSecond !== false) {
                // Entferne den zweiten 'send' Case komplett
                $content = substr_replace($content, "", $secondPos, $nextCaseAfterSecond - $secondPos);
            } else {
                // Der zweite 'send' Case ist der letzte, finde das Ende des switch-Statements
                $endPos = strpos($content, "}", $secondPos);
                if ($endPos !== false) {
                    $content = substr_replace($content, "", $secondPos, $endPos - $secondPos);
                }
            }
        }
    }
}

// Manuelle Bearbeitung, falls die automatische Erkennung nicht funktioniert
$pattern = '/\/\/ E-MAILS WURDEN GESENDET \(ohne Cron-Status-Update\).*?case \'send\':(.*?)\"</div>\",\s+\$processed,\s+\$total,\s+\$progress\s+\);/s';
$replacement = '';

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde aktualisiert. Der doppelte 'send' Case wurde entfernt.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}

// Erfolgsmeldung
echo "Korrektur abgeschlossen. Bitte die Newsletter-Seite neu laden.\n";
?>
