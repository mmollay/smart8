<?php
// Zeitzone setzen
date_default_timezone_set('Europe/Berlin');

// Datenbank-Konfiguration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'smart');
define('DB_PASS', 'Eiddswwenph21;');
define('DB_NAME', 'ssi_trader2');

// BitGet API Konfiguration
define('BITGET_API_URL', 'https://api.bitget.com');
define('BITGET_WS_URL', 'wss://ws.bitget.com/mix/v1/stream');

// Trading Konfiguration
define('DEFAULT_SYMBOL', 'ETHUSDT_UMCBL');
define('MIN_PRICE_DIFFERENCE', 1.0);
define('UPDATE_INTERVAL', 5000); // 5 Sekunden

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Automatische Ladung der Klassen
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Datenbank-Verbindung
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Verbindung fehlgeschlagen: " . $db->connect_error);
    }
    $db->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Datenbankfehler: " . $e->getMessage());
    die("Datenbankverbindung fehlgeschlagen");
}

// Hilfsfunktionen
function formatPrice($price)
{
    return number_format($price, 8, '.', '');
}

function validateSymbol($symbol)
{
    return preg_match('/^[A-Z0-9]+_UMCBL$/', $symbol) ? $symbol : DEFAULT_SYMBOL;
}

function logError($message, $context = [])
{
    error_log(date('Y-m-d H:i:s') . " - " . $message . " - " . json_encode($context));
}
