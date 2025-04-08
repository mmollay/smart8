<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die Tabellennamen
$replacements = [
    'newsletter_content' => 'email_contents',
    'newsletter_recipients' => 'recipients'
];

foreach ($replacements as $oldTable => $newTable) {
    $content = str_replace($oldTable, $newTable, $content);
}

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die korrekten Tabellennamen zu verwenden.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
