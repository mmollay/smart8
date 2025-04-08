<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Ersetze die SQL-Abfragen mit den korrekten Spaltennamen
$senderQueryPattern = [
    "s.name AS sender_name" => "CONCAT(s.first_name, ' ', s.last_name) AS sender_name",
    "s.reply_email AS reply_to_email" => "s.email AS reply_to_email",
    "s.reply_name AS reply_to_name" => "CONCAT(s.first_name, ' ', s.last_name) AS reply_to_name"
];

foreach ($senderQueryPattern as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert mit den korrekten Spaltennamen für die senders-Tabelle.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
