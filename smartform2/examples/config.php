<?php
// Definiere eine Konstante für sicheren Zugriff
define('SECURE_ACCESS', true);

// Datenbank-Konfiguration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'Jgewl21;',
    'database' => 'demo'
];

// Erstelle Datenbankverbindung
try {
    $db = new mysqli(
        $db_config['host'],
        $db_config['username'],
        $db_config['password'],
        $db_config['database']
    );

    // Setze Zeichensatz auf UTF-8
    $db->set_charset("utf8mb4");

    // Prüfe ob Verbindung erfolgreich
    if ($db->connect_error) {
        throw new Exception('Verbindung fehlgeschlagen: ' . $db->connect_error);
    }
} catch (Exception $e) {
    die('Fehler bei der Datenbankverbindung: ' . $e->getMessage());
}

// Allgemeine Konfigurationseinstellungen
$config = [
    // Debug-Einstellungen
    'debug' => true,
    'display_errors' => true,

    // Zeitzone
    'timezone' => 'Europe/Berlin',

    // Upload-Einstellungen
    'upload_dir' => __DIR__ . '/uploads/',
    'max_upload_size' => 5 * 1024 * 1024, // 5MB

    // Session-Einstellungen
    'session_lifetime' => 3600, // 1 Stunde

    // Export-Einstellungen
    'export' => [
        'default_format' => 'csv',
        'csv_delimiter' => ';',
        'csv_enclosure' => '"',
        'allowed_formats' => ['csv', 'xlsx', 'pdf']
    ],

    // Cache-Einstellungen
    'cache' => [
        'enabled' => true,
        'lifetime' => 3600,
        'path' => __DIR__ . '/cache/'
    ]
];

// Setze PHP-Einstellungen
if ($config['display_errors']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Setze Zeitzone
date_default_timezone_set($config['timezone']);

// Starte oder setze Session fort
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialisiere Upload-Verzeichnis falls notwendig
if (!file_exists($config['upload_dir'])) {
    mkdir($config['upload_dir'], 0777, true);
}

// Initialisiere Cache-Verzeichnis falls notwendig
if ($config['cache']['enabled'] && !file_exists($config['cache']['path'])) {
    mkdir($config['cache']['path'], 0777, true);
}

// Funktion zum sicheren Beenden der Datenbankverbindung
function closeDatabase()
{
    global $db;
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}

// Registriere Shutdown-Funktion
register_shutdown_function('closeDatabase');