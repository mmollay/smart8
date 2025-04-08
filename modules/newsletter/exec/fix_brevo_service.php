<?php
// Lese die Datei ein
$file = __DIR__ . '/../classes/BrevoEmailService.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von BrevoEmailService.php erstellt: $backup\n";

// Korrigiere die Name-Zuweisung in der setTo-Methode
$pattern = "/\\\$sendSmtpEmail->setTo\\(\\[\\['email' => \\\$recipient\\['email'\\], 'name' => \\\$recipient\\['name'\\] \?: ''\\]\\]\\);/";
$replacement = "\$sendSmtpEmail->setTo([[
                'email' => \$recipient['email'], 
                'name' => trim((\$recipient['firstname'] ?? '') . ' ' . (\$recipient['lastname'] ?? ''))
            ]]);";
$content = preg_replace($pattern, $replacement, $content);

// Korrigiere auch die Variable in der bind_param-Anweisung, um einen Referenzfehler zu beheben
$pattern = "/\\\$stmt->bind_param\\(\"is\", \\\$jobId, \\\$e->getMessage\\(\\)\\);/";
$replacement = "\$errorMessage = \$e->getMessage();
                    \$stmt->bind_param(\"is\", \$jobId, \$errorMessage);";
$content = preg_replace($pattern, $replacement, $content);

// Korrigiere die Rückgabewerte in sendSingleEmail
$pattern = "/return \\[.*?'success' => (true|false),.*?\\];/s";
$replacement = "if (\\1) {
                return [
                    'success' => \\1,
                    'message_id' => \$messageId,
                    'message' => 'Email sent successfully'
                ];
            } else {
                return [
                    'success' => \\1,
                    'message' => \$e->getMessage()
                ];
            }";
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "BrevoEmailService.php wurde aktualisiert, um den Empfängernamen korrekt zu setzen und Referenzfehler zu beheben.\n";
} else {
    echo "Fehler beim Aktualisieren von BrevoEmailService.php\n";
}

// Jetzt korrigieren wir auch die process_batch.php, um die Rückgabewerte korrekt zu verarbeiten
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Backup erstellen
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die Verwendung des Rückgabewerts von sendSingleEmail
$pattern = "/\\\$messageId = \\\$emailService->sendSingleEmail\\(.*?\\);/s";
$replacement = "\$result = \$emailService->sendSingleEmail(
            \$contentId,
            \$sender,
            \$recipient,
            \$subject,
            \$content,
            \$jobId,
            false
        );
        
        \$success = \$result['success'] ?? false;
        \$messageId = \$result['message_id'] ?? '';
        \$resultMessage = \$result['message'] ?? '';";
$content = preg_replace($pattern, $replacement, $content);

// Bearbeite die Ausgabe im Erfolgsfall
$pattern = "/'message' => \"E-Mail gesendet an {\\\$recipient\\['email'\\]} mit Message-ID: \\\$messageId\"/";
$replacement = "'message' => \"E-Mail gesendet an {\$recipient['email']} mit Message-ID: \$messageId. \$resultMessage\"";
$content = preg_replace($pattern, $replacement, $content);

// Bearbeite die Fehlerbehandlung
$pattern = "/\\\$errorMessage = \\\$e->getMessage\\(\\);/";
$replacement = "\$errorMessage = \$resultMessage ?: \$e->getMessage();";
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die Rückgabewerte von sendSingleEmail korrekt zu verarbeiten.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
