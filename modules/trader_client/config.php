<?php
// Überprüfen, ob die aktuell ausgeführte Datei nicht login2.php ist
$currentFile = basename($_SERVER['PHP_SELF']);

if ($currentFile !== "login2.php" && $currentFile !== "user_impersonation.php") {
    include(__DIR__ . "/check_permission.php");
}

$host = 'localhost';
$dbname = 'ssi_trader';
$username = 'smart';
$password = 'Eiddswwenph21;';

// Verbindung zur Datenbank herstellen
$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

// Fehlerbehandlung
if (!$db) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// Definieren Sie die Zeitfenster, die ausgeschlossen werden sollen
$exclusionPeriods = [
    ['start' => "2024-01-01", 'end' => "2024-05-31"],
    // Fügen Sie weitere Zeitfenster nach Bedarf hinzu
];

// Erstellen Sie eine Bedingung, die alle Zeitfenster ausschließt
$exclusionConditions = array_map(function ($period) {
    return '(o.time < UNIX_TIMESTAMP("' . $period['start'] . '") OR o.time > UNIX_TIMESTAMP("' . $period['end'] . '"))';
}, $exclusionPeriods);
$exclusionClause = implode(' AND ', $exclusionConditions);
