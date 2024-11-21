<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

include(__DIR__ . "/src/Helpers/functions.php");

// Verbindung zur Datenbank herstellen
$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_company';

$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

if (!$db) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// Session überprüfen
$userId = $_SESSION['client_id'] ?? null;
if (!$userId) {
    $userId = checkRememberMeToken($db) ? $_SESSION['client_id'] : null;
}

$userDetails = getUserDetails($userId, $db);