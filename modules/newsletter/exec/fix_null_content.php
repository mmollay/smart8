<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Füge Null-Prüfungen für subject und content hinzu
$pattern = "/\\\$subject = \\\$placeholderService->replacePlaceholders\(\\\$newsletter\['subject'\], \\\$recipient, \\\$customFields\);/";
$replacement = "// Sicherstellen, dass subject nicht null ist
        \$subject = \$newsletter['subject'] ?? 'Keine Betreffzeile';
        \$subject = \$placeholderService->replacePlaceholders(\$subject, \$recipient, \$customFields);";
$content = preg_replace($pattern, $replacement, $content);

$pattern = "/\\\$content = \\\$placeholderService->replacePlaceholders\(\\\$newsletter\['content'\], \\\$recipient, \\\$customFields\);/";
$replacement = "// Sicherstellen, dass content nicht null ist
        \$content = \$newsletter['content'] ?? 'Keine Inhalte';
        \$content = \$placeholderService->replacePlaceholders(\$content, \$recipient, \$customFields);";
$content = preg_replace($pattern, $replacement, $content);

// Überprüfen wir auch die Ladung des Newsletters selbst
// Zuerst Debugging-Ausgabe zum Anzeigen des geladenen Newsletters hinzufügen
$pattern = "/\\\$newsletter = \\\$result->fetch_assoc\(\);.*?writeLog\(\"Newsletter '{\\\.newsletter\['subject'\]}' geladen\", 'INFO'\);/s";
$replacement = "\$newsletter = \$result->fetch_assoc();
    writeLog(\"Newsletter geladen: \" . json_encode(\$newsletter), 'INFO');
    
    // Stelle sicher, dass wir alle erwarteten Felder haben
    if (!isset(\$newsletter['content']) || \$newsletter['content'] === null) {
        writeLog(\"Newsletter hat keinen Inhalt. Verwende html_content\", 'WARNING');
        // Versuche, html_content direkt zu verwenden, falls es existiert
        \$stmt = \$db->prepare(\"
            SELECT html_content
            FROM email_contents
            WHERE id = ?
        \");
        \$stmt->bind_param('i', \$contentId);
        \$stmt->execute();
        \$result = \$stmt->get_result();
        if (\$result->num_rows > 0) {
            \$htmlContent = \$result->fetch_assoc();
            \$newsletter['content'] = \$htmlContent['html_content'];
        }
    }
    
    writeLog(\"Newsletter '{\$newsletter['subject']}' geladen\", 'INFO');";
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um NULL-Werte für subject und content zu behandeln.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
