<?php
// src/bootstrap.php

// Fehlerreporting für Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Basis-Verzeichnis definieren
define('BASE_PATH', realpath(__DIR__ . '/..'));

// .env Datei laden
if (file_exists(BASE_PATH . '/.env')) {
    $envFile = file_get_contents(BASE_PATH . '/.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2) + [null, null];
            if (!empty($key)) {
                putenv(trim($key) . '=' . trim($value));
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

// Autoloader für Klassen
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/..';
    $class = str_replace(['Smart\\', '\\'], ['', '/'], $class);
    $file = $baseDir . '/src/' . $class . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Session starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datenbank-Konfiguration
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_DATABASE') ?: 'smart8'
];

try {
    // Datenbankverbindung
    $db = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    if ($db->connect_error) {
        throw new Exception("Verbindung fehlgeschlagen: " . $db->connect_error);
    }

    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    error_log($e->getMessage());
    die('Datenbankverbindung konnte nicht hergestellt werden. Bitte überprüfen Sie die Konfiguration.');
}

// Debug-Ausgabe der Konfiguration wenn im Entwicklungsmodus
if (getenv('APP_ENV') === 'development') {
    error_log("DB Config: " . print_r($dbConfig, true));
}