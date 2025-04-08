<?php
/**
 * Korrigiert die Anzeige des E-Mail-Status in der Datei newsletters.php
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

// Ersetze den 'pending' case, um die korrekte Reihenfolge der Überprüfungen zu gewährleisten
$pattern = '/case \'pending\':(.*?)return sprintf\((.*?)date\(\'d\.m\.Y H:i\', strtotime\(\$row\[\'created_at\'\]\)\)(.*?)\);(.*?)\/\/ VERSAND LÄUFT/s';

$replacement = 'case \'pending\':
                    // Überprüfe, ob E-Mails bereits gesendet wurden, obwohl der Status noch \'pending\' ist
                    $contentId = $row[\'content_id\'];
                    $stmt = $db->prepare("SELECT COUNT(*) as sent_count FROM email_jobs WHERE content_id = ? AND status = \'send\'");
                    $stmt->bind_param(\'i\', $contentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $sentCount = $result->fetch_assoc()[\'sent_count\'] ?? 0;
                    
                    if ($sentCount > 0) {
                        $total = getJobStats($row[\'content_id\'])[\'total\'] ?? 0;
                        $progress = $total > 0 ? round(($sentCount / $total) * 100) : 0;
                        
                        return sprintf(
                            "<div class=\'ui small text\'>
                                <i class=\'check circle icon\'></i> Gesendet<br>
                                <small class=\'ui grey text\'>%d von %d E-Mails erfolgreich zugestellt (%d%%)</small>
                            </div>",
                            $sentCount,
                            $total,
                            $progress
                        );
                    }
                    
                    // Falls keine E-Mails gesendet wurden, zeige "Warte auf Verarbeitung..."
                    return sprintf(
                        "<div class=\'ui small text\'>
                            <i class=\'clock outline icon\'></i> Warte auf Verarbeitung...<br>
                            <small class=\'ui grey text\'>Freigegeben am %s</small>
                        </div>",
                        date(\'d.m.Y H:i\', strtotime($row[\'created_at\']))
                    );

                // VERSAND LÄUFT';

$content = preg_replace($pattern, $replacement, $content);

// Entferne doppelte Überprüfungen (falls vorhanden)
$content = preg_replace('/\/\/ Überprüfe, ob E-Mails bereits gesendet wurden.*?}$/ms', '', $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde aktualisiert, um den Status 'send' korrekt anzuzeigen.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}

// Erfolgsmeldung
echo "Korrektur abgeschlossen. Bitte die Newsletter-Seite neu laden, um die korrekte Statusanzeige zu sehen.\n";
?>
