<?php


require_once __DIR__ . '/../vendor/autoload.php';

// Services importieddfren
use Smart\Core\Database;
use Smart\Core\Session;
use Smart\Services\AuthService;

try {
    // Datenbank-Konfiguration laden
    $dbConfig = require __DIR__ . '/../config/database.php';

    // Konfiguration validieren
    if (empty($dbConfig) || !is_array($dbConfig)) {
        throw new Exception('Ungültige Datenbank-Konfiguration');
    }

    // Prüfe erforderliche Konfigurationsschlüssel
    $required = ['host', 'username', 'password', 'database'];
    $missing = array_diff($required, array_keys($dbConfig));
    if (!empty($missing)) {
        throw new Exception('Fehlende Konfigurationsschlüssel: ' . implode(', ', $missing));
    }

    // Services initialisieren
    $db = Database::getInstance($dbConfig);
    $session = Session::getInstance($db);
    $auth = new AuthService($db);

} catch (Exception $e) {
    error_log("Bootstrap Error: " . $e->getMessage());
    die("Initialization Error: " . $e->getMessage());
}