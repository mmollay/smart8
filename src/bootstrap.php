<?php
// src/bootstrap.php
// Fehlerreporting für Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Basis-Verzeichnis definieren
define('BASE_PATH', realpath(__DIR__ . '/..'));


// .env Datei laden
if (file_exists(BASE_PATH . '/../.env')) {
    $envFile = file_get_contents(BASE_PATH . '/../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '#') !== 0) {
            // Verbesserte Trennung von Key und Value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Entferne Anführungszeichen falls vorhanden
                $value = trim($value, '"\'');

                if (!empty($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}
// Hilfsfunktion zum sicheren Abrufen von Umgebungsvariablen
function env($key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
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
    'host' => env('DB_HOST', 'localhost'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'database' => env('DB_DATABASE', 'smart8')
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
    if (env('APP_ENV') === 'development') {
        die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
    } else {
        die('Datenbankverbindung konnte nicht hergestellt werden. Bitte überprüfen Sie die Konfiguration.');
    }
}

// Debug-Ausgabe wenn im Entwicklungsmodus
if (env('APP_ENV') === 'development') {
    error_log("ENV Vars: " . print_r([
        'APP_ENV' => env('APP_ENV'),
        'DB_HOST' => env('DB_HOST'),
        'DB_USERNAME' => env('DB_USERNAME'),
        'DB_DATABASE' => env('DB_DATABASE')
    ], true));

    error_log("DB Config: " . print_r($dbConfig, true));
}

// Timezone setzen
date_default_timezone_set(env('APP_TIMEZONE', 'Europe/Vienna'));

// Fehlerbehandlung für Production
if (env('APP_ENV') !== 'development') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// Rückgabe der Datenbankverbindung
return $db;
