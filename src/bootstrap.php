<?php
// Basis-Autoloader und Fehlerbehandlung
require_once __DIR__ . '/../vendor/autoload.php';

// Logs-Verzeichnis erstellen falls nicht vorhanden
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// Logfile-Pfad definieren
define('LOG_FILE', $logDir . '/error.log');

// Prüfen ob Logfile beschreibbar ist
if (!file_exists(LOG_FILE)) {
    touch(LOG_FILE);
    chmod(LOG_FILE, 0666);
}

// Services importieren
use Smart\Core\Database;
use Smart\Core\Session;
use Smart\Services\AuthService;

try {
    // Lade .env Datei
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        // Erforderliche Umgebungsvariablen prüfen
        $dotenv->required([
            'DB_HOST',
            'DB_USERNAME',
            'DB_PASSWORD',
            'DB_DATABASE'
        ]);
    } else {
        throw new Exception('.env Datei nicht gefunden');
    }

    // Konfigurationen laden
    $dbConfig = [
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'database' => $_ENV['DB_DATABASE']
    ];

    // Services initialisieren
    $db = Database::getInstance($dbConfig);
    $session = Session::getInstance($db);
    $auth = new AuthService($db);

    // Error Handler registrieren
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorMessage = date('Y-m-d H:i:s') . " Error ($errno): $errstr in $errfile on line $errline\n";

        // Prüfen ob Logdatei beschreibbar
        if (is_writable(LOG_FILE)) {
            error_log($errorMessage, 3, LOG_FILE);
        }

        if (in_array($errno, [E_ERROR, E_USER_ERROR])) {
            die('Ein kritischer Fehler ist aufgetreten.');
        }

        return true;
    });

    // Exception Handler registrieren
    set_exception_handler(function ($e) {
        $errorMessage = date('Y-m-d H:i:s') . " Exception: " . $e->getMessage() .
            " in " . $e->getFile() . " on line " . $e->getLine() . "\n";

        // Prüfen ob Logdatei beschreibbar
        if (is_writable(LOG_FILE)) {
            error_log($errorMessage, 3, LOG_FILE);
        }

        if ($_ENV['APP_ENV'] ?? 'production' === 'development') {
            echo "<h1>Fehler</h1>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            die('Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Administrator.');
        }
    });

    // Session Timeout prüfen
    if ($session->isExpired()) {
        $auth->logout();
        header('Location: /auth/login.php');
        exit;
    }

} catch (Exception $e) {
    $errorMessage = "Bootstrap Error: " . $e->getMessage();

    // Prüfen ob Logdatei beschreibbar
    if (is_writable(LOG_FILE)) {
        error_log($errorMessage, 3, LOG_FILE);
    }

    if ($_ENV['APP_ENV'] ?? 'production' === 'development') {
        echo "Initialization Error: " . $e->getMessage();
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        die("Initialization Error");
    }
}

// Timezone setzen
date_default_timezone_set('Europe/Vienna');

// Basis-Konfiguration
define('APP_ROOT', dirname(__DIR__));
define('WEB_ROOT', ($_ENV['APP_ENV'] ?? 'production') === 'development' ? '/smart8' : '');
define('SMARTFORM_PATH', WEB_ROOT . '/smartform2');