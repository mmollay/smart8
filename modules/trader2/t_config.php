<?php
include(__DIR__ . '/../../get_env.php');

// Error Logging aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Log-Verzeichnis erstellen wenn es nicht existiert
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

$host = $_ENV['BINANCE_DB_HOST'];
$username = $_ENV['BINANCE_DB_USERNAME'];
$password = $_ENV['BINANCE_DB_PASSWORD'];
$database = $_ENV['BINANCE_DB_NAME'];

// Create connection
$db = new mysqli($host, $username, $password, $database);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Set charset to utf8
$db->set_charset("utf8");

// Debug-Funktion
function debug_log($message)
{
    error_log(print_r($message, true));
}


