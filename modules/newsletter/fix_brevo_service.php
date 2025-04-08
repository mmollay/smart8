<?php
// Lese die Datei ein
$file = __DIR__ . '/classes/BrevoEmailService.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von BrevoEmailService.php erstellt: $backup\n";

// Korrigiere die bind_param-Zeile mit Referenzübergabe
$pattern = '/\$stmt->bind_param\("is", \$jobId, "MessageID: " \. \$messageId\);/';
$replacement = '$messageIdText = "MessageID: " . $messageId;
                    $stmt->bind_param("is", $jobId, $messageIdText);';

$content = str_replace('$stmt->bind_param("is", $jobId, "MessageID: " . $messageId);', $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "BrevoEmailService.php wurde aktualisiert, um den Referenzfehler zu beheben.\n";
} else {
    echo "Fehler beim Aktualisieren von BrevoEmailService.php\n";
}
?>
