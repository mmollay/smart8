<?php
if (!isset($set_unsubscribed)) {
    // Session Check
    if (!defined('ALLOW_WEBHOOK') && php_sapi_name() !== 'cli') {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/no_access.php');
            exit;
        }
    }

    // Config einbinden für nicht-CLI und nicht-Webhook Zugriffe
    if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEBHOOK')) {
        require_once(__DIR__ . '/../../config.php');
    }
}

include(__DIR__ . "/../../get_env.php");

// APP_ROOT definieren
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $_ENV['APP_ROOT'] ?? dirname(__DIR__));
}

// Lade Konfiguration
$config = require(__DIR__ . '/config/config.php');

// Für einfacheren Zugriff
$newsletterDbConfig = $config['database'];
$packageConfig = $config['packages'];
$mailjetConfig = $config['mail']['mailjet'];
$eventTypes = $config['eventTypes'];

// Modul-spezifische Pfade
$isCliMode = php_sapi_name() === 'cli';

//upload path für Attachements der Emails 
$uploadBasePath = $_ENV['UPLOAD_PATH'] . '/' . $_SESSION['user_id'] . '/newsletters';

try {
    // Initialisierung der Newsletter-Datenbankverbindung
    $newsletterDb = mysqli_init();
    $newsletterDb->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    if (
        !$newsletterDb->real_connect(
            $newsletterDbConfig['host'],
            $newsletterDbConfig['username'],
            $newsletterDbConfig['password'],
            $newsletterDbConfig['dbname'],
            $newsletterDbConfig['port']
        )
    ) {
        throw new Exception('Newsletter-Datenbankverbindung fehlgeschlagen: ' . $newsletterDb->connect_error);
    }

    // UTF-8 Zeichensatz setzen
    $newsletterDb->set_charset('utf8mb4');

    // Überschreiben der globalen Datenbankverbindung für das Newsletter-Modul
    $db = $GLOBALS['mysqli'] = $GLOBALS['newsletter_db'] = $newsletterDb;
    $connection = $newsletterDb;  // Für Legacy-Code-Kompatibilität

} catch (Exception $e) {
    $errorMsg = "Newsletter-Datenbankfehler: " . $e->getMessage();
    error_log($errorMsg);
    die($errorMsg);
}

// Globale Variablen
$userId = $_SESSION['user_id'] ?? null;
$isAdmin = isset($_SESSION['superuser']) && $_SESSION['superuser'] == 1;

include __DIR__ . '/functions.php';

// Aufräumen beim Beenden
// register_shutdown_function(function () {
//     global $newsletterDb;
//     if (isset($newsletterDb)) {
//         $newsletterDb->close();
//     }
// });