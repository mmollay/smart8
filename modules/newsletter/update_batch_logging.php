<?php
/**
 * Dieses Script verbessert das Logging in der process_batch.php Datei
 */

// Pfad zur Zieldatei
$targetFile = __DIR__ . '/exec/process_batch.php';

if (!file_exists($targetFile)) {
    die("Zieldatei nicht gefunden: $targetFile\n");
}

// Dateiinhalt einlesen
$content = file_get_contents($targetFile);
if ($content === false) {
    die("Fehler beim Lesen der Datei: $targetFile\n");
}

// Backup erstellen
$backupFile = $targetFile . '.bak_' . date('YmdHis');
if (!file_put_contents($backupFile, $content)) {
    die("Fehler beim Erstellen des Backups: $backupFile\n");
}
echo "Backup erstellt: $backupFile\n";

// Änderung 1: Einfügen des speziellen Log-Verzeichnisses
$define_pattern = '/(define\(\'BASE_PATH\',.*?\);)/';
$define_replacement = '$1' . "\n\n" . 
    "// Spezifisches Log-Verzeichnis für Newsletter\n" .
    "define('NEWSLETTER_LOG_PATH', BASE_PATH . '/logs');\n" .
    "if (!is_dir(NEWSLETTER_LOG_PATH)) {\n" .
    "    mkdir(NEWSLETTER_LOG_PATH, 0755, true);\n" .
    "}\n";

$content = preg_replace($define_pattern, $define_replacement, $content);

// Änderung 2: Error-Log-Pfad anpassen
$error_log_pattern = '/(ini_set\(\'error_log\',\s*BASE_PATH\s*\.\s*\'\/logs\/batch_error\.log\'\);)/';
$error_log_replacement = "ini_set('error_log', NEWSLETTER_LOG_PATH . '/batch_error.log');";

$content = preg_replace($error_log_pattern, $error_log_replacement, $content);

// Änderung 3: WriteLog-Funktion verbessern
$write_log_pattern = '/(\$logMessage \.\= "\\n";)\s*(\$logFile = LOG_PATH \. \'\/cron_controller\.log\';)/';
$write_log_replacement = '$1' . "\n    " . 
    "// Verwende das Newsletter-spezifische Log-Verzeichnis\n    " .
    "\$logFile = NEWSLETTER_LOG_PATH . '/newsletter_batch.log';\n\n    " .
    "// Erstelle Log-Verzeichnis falls es nicht existiert\n    " .
    "if (!is_dir(NEWSLETTER_LOG_PATH)) {\n        " .
    "mkdir(NEWSLETTER_LOG_PATH, 0755, true);\n    " .
    "}";

$content = preg_replace($write_log_pattern, $write_log_replacement, $content);

// Änderung 4: Verbesserte Email-Logging hinzufügen
// Diese Änderung ist etwas komplexer und hängt stark vom genauen Aufbau der Datei ab
// Wir fügen daher eine Hilfsfunktion ein, die E-Mails ausführlicher protokolliert
$process_job_pattern = '/(function processJob\(\$db, \$emailService, \$placeholderService, \$contentId, \$jobId.*?\)\s*\{)/';
$process_job_replacement = '$1' . "\n    " . 
    "// Verbesserte Protokollierungsfunktion für E-Mails\n    " .
    "function logEmailResult(\$success, \$recipient, \$result, \$jobId) {\n        " .
    "if (\$success) {\n            " .
    "writeLog(\"E-Mail erfolgreich an \" . \$recipient['email'] . \" gesendet. Message-ID: \" . \$result['message_id'], 'INFO');\n        " .
    "} else {\n            " . 
    "\$errorMsg = \$result['error'] ?? 'Unbekannter Fehler beim Versenden der E-Mail';\n            " .
    "writeLog(\"Fehler beim Senden an \" . \$recipient['email'] . \": \" . \$errorMsg, 'ERROR');\n        " .
    "}\n    " .
    "}\n";

$content = preg_replace($process_job_pattern, $process_job_replacement, $content);

// Änderung 5: Logging vor dem E-Mail-Versand hinzufügen
$send_email_pattern = '/(\$message = \$placeholderService->replacePlaceholders\(\$jobData\[\'message\'\], \$recipient\);)/';
$send_email_replacement = '$1' . "\n\n        " . 
    "// Verbessertes Logging: Details über E-Mail-Versand\n        " .
    "writeLog(\"Sende E-Mail an: \" . \$recipient['email'] . \" (Job ID: \$jobId)\", 'INFO');";

$content = preg_replace($send_email_pattern, $send_email_replacement, $content);

// Änderung 6: Verbesserte Erfolgs-/Fehlerbehandlung nach dem E-Mail-Versand
$email_result_pattern = '/if \(\$result\[\'success\'\]\) \{\s*\/\/ Job als \'sent\' markieren/';
$email_result_replacement = "if (\$result['success']) {\n            " . 
    "// Verbessertes Logging: Erfolgsstatus\n            " . 
    "writeLog(\"E-Mail erfolgreich an \" . \$recipient['email'] . \" gesendet. Message-ID: \" . \$result['message_id'], 'INFO');\n\n            " . 
    "// Job als 'sent' markieren";

$content = preg_replace($email_result_pattern, $email_result_replacement, $content);

// Änderung 7: Verbesserte Fehlerbehandlung
$error_pattern = '/} else \{\s*throw new Exception\(\$result\[\'error\'\] \?\? \'Unbekannter Fehler beim Versenden der E-Mail\'\);/';
$error_replacement = "} else {\n            " . 
    "// Detaillierte Fehlerprotokollierung\n            " . 
    "\$errorMsg = \$result['error'] ?? 'Unbekannter Fehler beim Versenden der E-Mail';\n            " . 
    "writeLog(\"Fehler beim Senden an \" . \$recipient['email'] . \": \" . \$errorMsg, 'ERROR');\n            " . 
    "throw new Exception(\$errorMsg);";

$content = preg_replace($error_pattern, $error_replacement, $content);

// Änderung 8: E-Mail-Adresse mit in den Job-Verarbeitungs-Loop aufnehmen
$check_stmt_pattern = '/(\$checkStmt = \$db->prepare\("\\s*SELECT r.unsubscribed, ej.status)/';
$check_stmt_replacement = '$1, r.email';

$content = preg_replace($check_stmt_pattern, $check_stmt_replacement, $content);

// Änderung 9: E-Mail-Adresse im Logging verwenden
$job_log_pattern = '/(writeLog\("Job ID \$jobId nicht gefunden", \'ERROR\'\);)/';
$job_log_replacement = '$1' . "\n            " . 
    "writeLog(\"Verarbeite Job \$jobId für Empfänger: \" . \$result['email'], 'INFO');";

$content = preg_replace($job_log_pattern, $job_log_replacement, $content);

// Änderung 10: E-Mail-Adresse in den Unsubscribe-Log aufnehmen
$unsubscribe_pattern = '/(writeLog\("Job ID \$jobId übersprungen - Empfänger abgemeldet", \'INFO\'\);)/';
$unsubscribe_replacement = "writeLog(\"Job ID \$jobId übersprungen - Empfänger \" . \$result['email'] . \" abgemeldet\", 'INFO');";

$content = preg_replace($unsubscribe_pattern, $unsubscribe_replacement, $content);

// Datei mit den Änderungen speichern
if (!file_put_contents($targetFile, $content)) {
    die("Fehler beim Schreiben der Änderungen in die Datei: $targetFile\n");
}

echo "Änderungen erfolgreich in die Datei eingefügt: $targetFile\n";
echo "Du kannst die verbesserte Datei jetzt verwenden, um detailliertere Logs über den E-Mail-Versand zu erhalten.\n";
echo "Die Logs werden jetzt im Verzeichnis " . realpath(__DIR__) . "/logs/ gespeichert.\n";
echo "Falls etwas nicht wie erwartet funktioniert, kannst du mit dem erstellten Backup wieder zum ursprünglichen Zustand zurückkehren.\n";
