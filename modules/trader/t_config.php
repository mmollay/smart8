<?php
//include(__DIR__ . '/../../config.php');
include_once(__DIR__ . '/functions.inc.php');
error_reporting(E_ALL);
ini_set('', 1);

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_trader';

$GLOBALS['db'] = $db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);


$arrayContracts_save = array(
    "GER30" => "DAX 30",
    "NAS100" => "NASDAQ 100",
);

$arrayContracts = array(
    "GER30" => "GER30",
    "NAS100" => "NAS100",
);

// Definieren Sie die Zeitfenster, die ausgeschlossen werden sollen
$exclusionPeriods = [
    ['start' => "2024-04-15", 'end' => "2024-04-19"],
    // Fügen Sie weitere Zeitfenster nach Bedarf hinzu
];

if (!mysqli_select_db($db, 'ssi_trader')) {

    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}

// Erstellen Sie eine Bedingung, die alle Zeitfenster ausschließt
$exclusionConditions = array_map(function ($period) {
    return '(o.time < UNIX_TIMESTAMP("' . $period['start'] . '") OR o.time > UNIX_TIMESTAMP("' . $period['end'] . '"))';
}, $exclusionPeriods);
$exclusionClause = implode(' AND ', $exclusionConditions);
