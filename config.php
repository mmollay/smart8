<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Überprüfen, ob die aktuell ausgeführte Datei nicht login2.php ist
$currentFile = basename($_SERVER['PHP_SELF']);

// if ($currentFile !== "login2.php" && $currentFile !== "user_impersonation.php") {
//     include (__DIR__ . "/check_permission.php");
// }

include (__DIR__ . "/functions.php");

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_company'; //Basis Db

$version = '8.0.0';

// Verbindung zur Datenbank herstellen
$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);


// Fehlerbehandlung
if (!$db) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

$userId = $_SESSION['client_id'] ?? null;

// Funktion zum Abrufen von Benutzerdetails
//Bsp.: $firstname = $userDetails['firstname'];
$userDetails = getUserDetails($userId, $db);
$_SESSION['faktura_company_id'] = $company_id = $userDetails['company_id'];

