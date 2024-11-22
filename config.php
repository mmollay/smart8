<?php
// config.php

// Basis-Einstellungen
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Autoloader und Funktionen einbinden
require_once __DIR__ . '/vendor/autoload.php';
include(__DIR__ . "/src/Helpers/functions.php");

try {
    // .env Datei laden
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Datenbank-Konfiguration laden
    $dbConfig = require_once __DIR__ . '/config/database.php';

    // Session starten
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Datenbankverbindung herstellen
    $db = $GLOBALS['mysqli'] = mysqli_connect(
        $_ENV['DB_HOST'] ?? $dbConfig['host'],
        $_ENV['DB_USERNAME'] ?? $dbConfig['username'],
        $_ENV['DB_PASSWORD'] ?? $dbConfig['password'],
        $_ENV['DB_DATABASE'] ?? $dbConfig['dbname']
    );

    if (!$db) {
        throw new Exception("Datenbankverbindung fehlgeschlagen: " . mysqli_connect_error());
    }

    // Zeichensatz setzen
    mysqli_set_charset($db, 'utf8mb4');

    // Basis-Konstanten definieren
    define('APP_ROOT', __DIR__);
    define('WEB_ROOT', $_ENV['APP_ENV'] === 'development' ? '/smart8' : '');
    define('SMARTFORM_PATH', WEB_ROOT . '/smartform2');

    // Session und Benutzer-Authentifizierung
    $userId = $_SESSION['client_id'] ?? null;
    if (!$userId) {
        $userId = checkRememberMeToken($db) ? $_SESSION['client_id'] : null;
    }

    // Benutzerdetails laden wenn authentifiziert
    $userDetails = $userId ? getUserDetails($userId, $db) : null;

    // Globale Variablen für Abwärtskompatibilität
    $GLOBALS['connection'] = $db;

} catch (Exception $e) {
    // Fehlerbehandlung
    error_log("Konfigurationsfehler: " . $e->getMessage());

    if ($_ENV['APP_ENV'] === 'development') {
        die("Konfigurationsfehler: " . $e->getMessage());
    } else {
        die("Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Administrator.");
    }
}

// Hilfsfunktionen

/**
 * Prüft ob die Anwendung im Debug-Modus läuft
 */
function isDebug(): bool
{
    return $_ENV['APP_ENV'] === 'development';
}

/**
 * Sichere Datenbankabfrage
 */
function dbQuery($query, $params = []): mysqli_result|bool
{
    global $db;

    if (empty($params)) {
        return mysqli_query($db, $query);
    }

    $stmt = mysqli_prepare($db, $query);
    if ($stmt === false) {
        throw new Exception("Query Vorbereitung fehlgeschlagen: " . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}

/**
 * Sicheres Beenden der Anwendung
 */
function shutdown(): void
{
    global $db;

    if (isset($db) && $db) {
        mysqli_close($db);
    }
}

// Shutdown-Funktion registrieren
register_shutdown_function('shutdown');