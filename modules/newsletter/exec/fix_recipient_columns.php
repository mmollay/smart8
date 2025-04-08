<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Korrigiere die Spaltennamen in der SQL-Abfrage
$pattern = "/r\.email, r\.firstname, r\.lastname/";
$replacement = "r.email, r.first_name, r.last_name";
$content = preg_replace($pattern, $replacement, $content);

// Korrigiere auch die Variablennamen im Code
$pattern = "/\\\$recipient = \[.*?'firstname' => \\\$job\['firstname'\] \?: '',.*?'lastname' => \\\$job\['lastname'\] \?: '',.*?\];/s";
$replacement = "\$recipient = [
            'email' => \$job['email'],
            'firstname' => \$job['first_name'] ?? '',
            'lastname' => \$job['last_name'] ?? '',
            'title' => \$job['title'] ?? '',
            'gender' => \$job['gender'] ?? '',
            'company' => \$job['company'] ?? ''
        ];";
$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert mit den korrekten Spaltennamen für die recipients-Tabelle.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
