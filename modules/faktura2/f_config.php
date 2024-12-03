<?php
// Grundlegende Modul-Konfiguration
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Einbindung der Hauptkonfigurationsdateien
require_once(__DIR__ . '/../../config.php');
//require_once(__DIR__ . '/functions.inc.php');

// Faktura2-spezifische Datenbankkonfiguration
$fakturaDbConfig = [
    'host' => $_ENV['FAKTURA_DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['FAKTURA_DB_PORT'] ?? 3306,
    'username' => $_ENV['FAKTURA_DB_USERNAME'] ?? '',
    'password' => $_ENV['FAKTURA_DB_PASSWORD'] ?? '',
    'dbname' => $_ENV['FAKTURA_DB_NAME'] ?? 'ssi_fakturaV2'
];

try {
    // Initialisierung der Faktura2-Datenbankverbindung
    $fakturaDb = mysqli_init();
    $fakturaDb->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    if (
        !$fakturaDb->real_connect(
            $fakturaDbConfig['host'],
            $fakturaDbConfig['username'],
            $fakturaDbConfig['password'],
            $fakturaDbConfig['dbname'],
            $fakturaDbConfig['port']
        )
    ) {
        throw new Exception('Faktura2-Datenbankverbindung fehlgeschlagen: ' . $fakturaDb->connect_error);
    }

    // UTF-8 Zeichensatz setzen
    $fakturaDb->set_charset('utf8mb4');

    // Überschreiben der globalen Datenbankverbindung für das Faktura2-Modul
    $db = $GLOBALS['mysqli'] = $GLOBALS['faktura_db'] = $fakturaDb;
    $connection = $fakturaDb;  // Für Legacy-Code-Kompatibilität

} catch (Exception $e) {
    $errorMsg = "Faktura2-Datenbankfehler: " . $e->getMessage();
    error_log($errorMsg);
    die($errorMsg);
}

// Hilfsfunktionen für das Faktura2-Modul
function getAccountArray($db)
{
    $accounts = [];
    $query = "SELECT 
                account_id, 
                CONCAT(account_number, ' - ', account_name, ' (', IFNULL(percentage, ''), '%)') AS account_display
              FROM accounts
              WHERE account_type IN ('Income', 'Expense')
              ORDER BY account_number";

    $result = $db->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $accounts[$row['account_id']] = $row['account_display'];
        }
        $result->free();
    }
    return $accounts;
}

function getArticleArray($db)
{
    $articles = [];
    $query = "SELECT 
                article_id, 
                article_number, 
                name 
              FROM articles 
              ORDER BY name";

    $result = $db->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $articles[$row['article_id']] = $row['article_number'] . ' - ' . $row['name'];
        }
        $result->free();
    }
    return $articles;
}