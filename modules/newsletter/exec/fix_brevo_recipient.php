<?php
// Lese die Datei ein
$file = __DIR__ . '/../classes/BrevoEmailService.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von BrevoEmailService.php erstellt: $backup\n";

// Zeige die aktuelle Implementierung der setTo-Methode
$setToMethodPattern = "/(\\s*\\\$sendSmtpEmail->setTo\\(.*?\\);)/s";
if (preg_match($setToMethodPattern, $content, $matches)) {
    echo "Aktuelle setTo-Implementierung: " . $matches[1] . "\n";
}

// Ersetze den ganzen Block der Empfänger-Zuweisung, um sicherzustellen, dass 'name' immer gesetzt ist
$newToMethod = "
            // Set recipient - ensure name is set to avoid Brevo API error
            \$recipientName = trim((\$recipient['firstname'] ?? '') . ' ' . (\$recipient['lastname'] ?? ''));
            // If name is empty, use the email address as name
            if (empty(\$recipientName)) {
                \$recipientName = \$recipient['email'];
            }
            
            \$sendSmtpEmail->setTo([
                [
                    'email' => \$recipient['email'],
                    'name' => \$recipientName
                ]
            ]);";

$content = preg_replace($setToMethodPattern, $newToMethod, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "BrevoEmailService.php wurde aktualisiert, um sicherzustellen, dass der Empfängername immer gesetzt ist.\n";
} else {
    echo "Fehler beim Aktualisieren von BrevoEmailService.php\n";
}
?>
