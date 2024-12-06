<?php
// config.php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
include(__DIR__ . "/src/Helpers/functions.php");

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $dbConfig = require_once __DIR__ . '/config/database.php';

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

    mysqli_set_charset($db, 'utf8mb4');

    define('APP_ROOT', __DIR__);
    //define('WEB_ROOT', $_ENV['APP_ENV'] === 'development' ? '/smart8' : '');
    define('WEB_ROOT', $_ENV['WEB_ROOT']);

    define('SMARTFORM_PATH', WEB_ROOT . '/smartform2');

    // Benutzer-Authentifizierung und Zugriffskontrolle
    $userId = $_SESSION['user_id'] = $_SESSION['client_id'] ?? null;

    if (!$userId) {
        $userId = checkRememberMeToken($db) ? $_SESSION['client_id'] : null;
    }

    // Benutzerdetails laden
    $userDetails = $userId ? getUserDetails($userId, $db) : null;
    $_SESSION['superuser'] = $isSuperuser = $userDetails['superuser'] ?? false;

    // Modulzugriffssteuerung
    function checkModuleAccess($moduleIdentifier)
    {
        global $db, $userId, $isSuperuser;

        // Main-Modul ist immer verfügbar
        if ($moduleIdentifier === 'main') {
            return true;
        }

        if ($isSuperuser) {
            return true;
        }

        if (!$userId) {
            return false;
        }

        $query = "
            SELECT 1 
            FROM modules m
            JOIN user_modules um ON m.module_id = um.module_id
            WHERE m.identifier = ? 
            AND um.user_id = ?
            AND um.status = 1
            AND m.status = 1
            LIMIT 1
        ";

        $stmt = $db->prepare($query);
        $stmt->bind_param('si', $moduleIdentifier, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Automatische Zugriffsprüfung für das aktuelle Modul
    function enforceModuleAccess()
    {
        $currentPath = $_SERVER['SCRIPT_NAME'];
        $pathParts = explode('/', $currentPath);

        // Suche nach dem Modulnamen im Pfad
        $moduleIndex = array_search('modules', $pathParts);
        if ($moduleIndex !== false && isset($pathParts[$moduleIndex + 1])) {
            $currentModule = $pathParts[$moduleIndex + 1];

            // Main-Modul ist immer verfügbar
            if ($currentModule === 'main') {
                return true;
            }

            if (!checkModuleAccess($currentModule)) {
                // Weiterleitung zur Fehlerseite bei fehlendem Zugriff
                header("Location: " . WEB_ROOT . "/auth/no_access.php");
                exit;
            }
        }
    }

    // Führe die Zugriffsprüfung automatisch durch
    enforceModuleAccess();

    $GLOBALS['connection'] = $db;

} catch (Exception $e) {
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
//register_shutdown_function('shutdown');