<?php
// Lese die Datei ein
$file = __DIR__ . '/process_batch.php';
$content = file_get_contents($file);

// Erstelle ein Backup
$backup = $file . '.bak_' . date('YmdHis');
file_put_contents($backup, $content);
echo "Backup von process_batch.php erstellt: $backup\n";

// Erstelle die writeLog-Funktion und füge sie vor den ersten Gebrauch ein
$pattern = '/\/\/ Prozess-ID setzen/';
$replacement = '// Log-Funktion definieren
function writeLog($message, $level = \'INFO\', $writeToConsole = false, $logFile = null) {
    global $batchLogFile;
    $timestamp = date(\'Y-m-d H:i:s\');
    $logMessage = "[$timestamp] [$level] $message\n";
    
    if ($logFile) {
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    } elseif (isset($batchLogFile)) {
        file_put_contents($batchLogFile, $logMessage, FILE_APPEND);
    }
    
    if ($writeToConsole) {
        echo $logMessage;
    }
}

// Prozess-ID setzen';

$content = preg_replace($pattern, $replacement, $content);

// Speichere die Datei
if (file_put_contents($file, $content)) {
    echo "process_batch.php wurde aktualisiert, um die writeLog-Funktion hinzuzufügen.\n";
} else {
    echo "Fehler beim Aktualisieren von process_batch.php\n";
}
?>
