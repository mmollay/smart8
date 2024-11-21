<?
// config/config.php
session_start();
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Lade Datenbank-Konfiguration
$dbConfig = require_once __DIR__ . '/database.php';

// Datenbankverbindung herstellen
$db = $GLOBALS['mysqli'] = mysqli_connect(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['dbname']
);

if (!$db) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// Lade Hilfsfunktionen
require_once __DIR__ . '/../src/Helpers/functions.php';

// Session überprüfen
$userId = $_SESSION['client_id'] ?? null;
$userDetails = $userId ? getUserDetails($userId, $db) : null;