<?php
// Lese die Datei ein
$file = __DIR__ . "/../lists/newsletters.php";
$content = file_get_contents($file);

// Backup bereits erstellt im vorherigen Schritt

// Checke, ob der case "send" bereits existiert
if (strpos($content, "case 'send'") === false) {
    // Aktualisiere die switch-Anweisung, um "send"-Status zu berücksichtigen
    $pattern = "/(case 'running'.*?);(\s*\/\/ VERSAND ABGESCHLOSSEN\s*case 'completed')/s";
    $replacement = "$1;\n\n                // E-MAILS WURDEN GESENDET (ohne Cron-Status-Update)\n                case 'send':\n                    $jobStats = getJobStats($row['content_id']);\n                    $total = $jobStats['total'] ?? 0;\n                    $processed = $jobStats['processed'] ?? 0;\n                    $progress = $total > 0 ? round(($processed / $total) * 100) : 0;\n                    \n                    return sprintf(\n                        \"<div class='ui small text'>\n                            <i class='check circle icon'></i> Gesendet<br>\n                            <small class='ui grey text'>%d von %d E-Mails erfolgreich zugestellt (%d%%)</small>\n                        </div>\",\n                        $processed,\n                        $total,\n                        $progress\n                    );\n                $2";
    
    $content = preg_replace($pattern, $replacement, $content);
}

// Anpassen des Falls "pending" für bessere Statuserkennung
if (strpos($content, "Überprüfe, ob E-Mails bereits gesendet wurden") === false) {
    $pendingPattern = "/(case 'pending'.*?return sprintf.*?);/s";
    $pendingReplacement = "$1;\n\n                    // Überprüfe, ob E-Mails bereits gesendet wurden, obwohl der Status noch 'pending' ist\n                    $contentId = $row['content_id'];\n                    $stmt = $db->prepare(\"SELECT COUNT(*) as sent_count FROM email_jobs WHERE content_id = ? AND status = 'send'\");\n                    $stmt->bind_param('i', $contentId);\n                    $stmt->execute();\n                    $result = $stmt->get_result();\n                    $sentCount = $result->fetch_assoc()['sent_count'] ?? 0;\n                    \n                    if ($sentCount > 0) {\n                        $total = getJobStats($row['content_id'])['total'] ?? 0;\n                        $progress = $total > 0 ? round(($sentCount / $total) * 100) : 0;\n                        \n                        return sprintf(\n                            \"<div class='ui small text'>\n                                <i class='check circle icon'></i> Gesendet<br>\n                                <small class='ui grey text'>%d von %d E-Mails erfolgreich zugestellt (%d%%)</small>\n                            </div>\",\n                            $sentCount,\n                            $total,\n                            $progress\n                        );\n                    }";
    
    $content = preg_replace($pendingPattern, $pendingReplacement, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde aktualisiert, um den Status 'send' korrekt anzuzeigen.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}
?>