<?php
/**
 * Aktualisiert start_cron.php, um richtige Parameter zu übergeben
 */

$file = __DIR__ . '/ajax/start_cron.php';
$content = file_get_contents($file);

if (!$content) {
    die("Fehler beim Lesen von $file");
}

// Backup erstellen
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup erstellt: $backup\n";

// Erhalte die neuesten ausstehenden E-Mail-Jobs
$db_query = <<<SQL
// Hole die neuesten aktiven Newsletter und pending Jobs
\$stmt = \$db->prepare("
    SELECT ec.id AS content_id, GROUP_CONCAT(ej.id) AS job_ids
    FROM email_contents ec
    JOIN email_jobs ej ON ec.id = ej.content_id
    WHERE ec.send_status = 1
    AND ej.status = 'pending'
    GROUP BY ec.id
    ORDER BY ec.created_at DESC
    LIMIT 1
");
\$stmt->execute();
\$result = \$stmt->get_result();

if (\$result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Keine ausstehenden E-Mail-Jobs gefunden',
    ]);
    exit;
}

\$row = \$result->fetch_assoc();
\$contentId = \$row['content_id'];
\$jobIds = \$row['job_ids'];
SQL;

// Aktualisiere den Befehl, der ausgeführt wird
$command_replacement = <<<'EOT'
$command = '';
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $command = "export PATH=/Applications/XAMPP/xamppfiles/bin:/usr/local/bin:/usr/bin:/bin && cd " . __DIR__ . "/../exec && php process_batch.php --content-id=$contentId --job-ids=$jobIds 2>&1";
} else {
    $command = "cd " . __DIR__ . "/../exec && /usr/bin/php process_batch.php --content-id=$contentId --job-ids=$jobIds 2>&1";
}
EOT;

// Füge die neue DB-Abfrage nach dem Header und vor dem Kommando ein
$pattern1 = '/(header\(\'Content-Type: application\/json\'\);.*?if \(!\\$isAdmin\) \{.*?}\s*?)/s';
$replacement1 = '$1' . "\n\n" . $db_query . "\n\n";
$content = preg_replace($pattern1, $replacement1, $content);

// Ersetze den Befehl
$pattern2 = '/\$command = \'\';.*?if \(\$_SERVER\[\'SERVER_NAME\'\] === \'localhost\'\) \{.*?\$command = ".*?";.*?} else \{.*?\$command = ".*?";.*?}/s';
$replacement2 = $command_replacement;
$content = preg_replace($pattern2, $replacement2, $content);

// Datei speichern
if (file_put_contents($file, $content)) {
    echo "start_cron.php wurde aktualisiert, um die richtigen Parameter an process_batch.php zu übergeben.\n";
    echo "Jetzt werden automatisch die neuesten ausstehenden E-Mail-Jobs für den Versand ausgewählt.\n";
} else {
    echo "Fehler beim Schreiben in $file\n";
}
?>
