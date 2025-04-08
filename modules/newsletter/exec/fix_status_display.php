<?php
// Lese die Datei ein
$file = __DIR__ . '/../lists/newsletters.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von newsletters.php erstellt: $backup\n";

// Aktualisiere die switch-Anweisung, um "send"-Status zu berücksichtigen
// Wir fügen einen neuen Fall "send" nach "running" hinzu
$pattern = "/(case 'running'.*?);(\s*\/\/ VERSAND ABGESCHLOSSEN\s*case 'completed')/s";
$replacement = "$1;\n\n                // E-MAILS WURDEN GESENDET (ohne Cron-Status-Update)\n                case 'send':\n                    \$jobStats = getJobStats(\$row['content_id']);\n                    \$total = \$jobStats['total'] ?? 0;\n                    \$processed = \$jobStats['processed'] ?? 0;\n                    \$progress = \$total > 0 ? round((\$processed / \$total) * 100) : 0;\n                    \n                    return sprintf(\n                        \"<div class='ui small text'>\n                            <i class='check circle icon'></i> Gesendet<br>\n                            <small class='ui grey text'>%d von %d E-Mails erfolgreich zugestellt (%d%%)</small>\n                        </div>\",\n                        \$processed,\n                        \$total,\n                        \$progress\n                    );\n                $2";

// Anpassen des Falls 'pending' für bessere Statuserkennung
$pendingPattern = "/(case 'pending'.*?return sprintf.*?);/s";
$pendingReplacement = "$1;\n\n                    // Überprüfe, ob E-Mails bereits gesendet wurden, obwohl der Status noch 'pending' ist\n                    \$db = getDatabase();\n                    \$contentId = \$row['content_id'];\n                    \$stmt = \$db->prepare(\"SELECT COUNT(*) as sent_count FROM email_jobs WHERE content_id = ? AND status = 'send'\");\n                    \$stmt->bind_param('i', \$contentId);\n                    \$stmt->execute();\n                    \$result = \$stmt->get_result();\n                    \$sentCount = \$result->fetch_assoc()['sent_count'] ?? 0;\n                    \n                    if (\$sentCount > 0) {\n                        \$total = getJobStats(\$row['content_id'])['total'] ?? 0;\n                        \$progress = \$total > 0 ? round((\$sentCount / \$total) * 100) : 0;\n                        \n                        return sprintf(\n                            \"<div class='ui small text'>\n                                <i class='check circle icon'></i> Gesendet<br>\n                                <small class='ui grey text'>%d von %d E-Mails erfolgreich zugestellt (%d%%)</small>\n                            </div>\",\n                            \$sentCount,\n                            \$total,\n                            \$progress\n                        );\n                    }";

$content = preg_replace($pattern, $replacement, $content);
$content = preg_replace($pendingPattern, $pendingReplacement, $content);

// Möglicherweise fehlende getDatabase-Funktion hinzufügen
if (!preg_match('/function getDatabase\(\)/', $content)) {
    $pattern = "/(function getJobStats.*?})/s";
    $replacement = "$1\n\nfunction getDatabase() {\n    global \$db;\n    if (!\$db) {\n        require_once __DIR__ . '/../n_config.php';\n    }\n    return \$db;\n}";
    $content = preg_replace($pattern, $replacement, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "newsletters.php wurde aktualisiert, um den Status 'send' korrekt anzuzeigen.\n";
} else {
    echo "Fehler beim Aktualisieren von newsletters.php\n";
}
?>
