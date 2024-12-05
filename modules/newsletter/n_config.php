<?php
// Grundlegende Modul-Konfiguration
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Session und Zugriffskontrolle
// if (!defined('ALLOW_WEBHOOK') && php_sapi_name() !== 'cli') {
//     session_start([
//         'cookie_httponly' => true,
//         'cookie_secure' => true,
//         'cookie_samesite' => 'Strict'
//     ]);

//     // if (!isset($_SESSION['user_id'])) {
//     //     header('Location: /auth/no_access.php');
//     //     exit;
//     // }
// }

if (!$set_unsubscribed) {
    // Config einbinden für nicht-CLI und nicht-Webhook Zugriffe
    if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEBHOOK')) {
        require_once(__DIR__ . '/../../config.php');
    }
}

loadEnvFile(__DIR__ . '/../../.env');

// APP_ROOT definieren
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $_ENV['APP_ROOT'] ?? dirname(__DIR__));
}

// Newsletter-spezifische Datenbankkonfiguration
$newsletterDbConfig = [
    'host' => $_ENV['NEWSLETTER_DB_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['NEWSLETTER_DB_PORT'] ?? 3306),
    'username' => $_ENV['NEWSLETTER_DB_USERNAME'] ?? 'root',
    'password' => $_ENV['NEWSLETTER_DB_PASSWORD'] ?? '',
    'dbname' => $_ENV['NEWSLETTER_DB_NAME'] ?? 'ssi_newsletter'
];

// E-Mail-Konfiguration für Mailjet
$mailjetConfig = [
    'api_key' => $_ENV['MAILJET_API_KEY'] ?? '',
    'api_secret' => $_ENV['MAILJET_API_SECRET'] ?? ''
];

// Modul-spezifische Pfade
$isCliMode = php_sapi_name() === 'cli';
$uploadBasePath = $isCliMode || $_SERVER['SERVER_NAME'] === 'localhost'
    ? $_ENV['APP_ROOT'] . '/uploads/users/'
    : $_ENV['UPLOAD_PATH'];



// Datenbankverbindung initialisieren
function initializeDatabase($config)
{
    try {
        $db = mysqli_init();

        if (!$db) {
            throw new Exception('mysqli_init fehlgeschlagen');
        }

        // Verbindungsoptionen setzen
        $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        $db->options(MYSQLI_OPT_READ_TIMEOUT, 30);
        $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

        // Verbindung herstellen
        if (
            !$db->real_connect(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['dbname'],
                $config['port']
            )
        ) {
            throw new Exception('Datenbankverbindung fehlgeschlagen: ' . $db->connect_error);
        }

        // Zeichensatz und Einstellungen
        $db->set_charset('utf8mb4');
        $db->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

        return $db;

    } catch (Exception $e) {
        error_log("Datenbankfehler: " . $e->getMessage());
        throw $e;
    }
}

try {
    $newsletterDb = initializeDatabase($newsletterDbConfig);
    $db = $GLOBALS['mysqli'] = $GLOBALS['newsletter_db'] = $newsletterDb;
    $connection = $newsletterDb;
} catch (Exception $e) {
    die("Datenbankverbindung konnte nicht hergestellt werden.");
}

// Hilfsfunktionen
function getDefaultPlaceholders($customEmail = null)
{
    $now = new DateTime();
    return [
        'vorname' => 'Max',
        'nachname' => 'Mustermann',
        'titel' => 'Dr.',
        'geschlecht' => 'Herr',
        'anrede' => 'Sehr geehrter Herr Dr. Mustermann',
        'firma' => 'Demo GmbH',
        'company' => 'Demo GmbH',
        'email' => $customEmail ?? 'max.mustermann@beispiel.de',
        'datum' => $now->format('d.m.Y'),
        'datum_lang' => $now->format('l, d. F Y'),
        'uhrzeit' => $now->format('H:i'),
        'uhrzeit_lang' => $now->format('H:i:s'),
        'datum_kurz' => $now->format('d.m.y'),
        'monat' => $now->format('F'),
        'jahr' => $now->format('Y'),
        'wochentag' => $now->format('l')
    ];
}

function replacePlaceholders($text, $customPlaceholders = [])
{
    if (empty($text))
        return '';

    $placeholders = array_merge(
        getDefaultPlaceholders($customPlaceholders['email'] ?? null),
        $customPlaceholders
    );

    return strtr($text, array_reduce(
        array_keys($placeholders),
        function ($carry, $key) use ($placeholders) {
            $carry['{{' . $key . '}}'] = $placeholders[$key];
            return $carry;
        },
        []
    ));
}

function getAllGroups($db)
{
    global $userId;

    if (!$userId) {
        error_log("getAllGroups: Keine User-ID verfügbar");
        return [];
    }

    try {
        $query = "
            SELECT 
                g.id,
                g.name,
                g.color,
                COUNT(DISTINCT CASE 
                    WHEN r.unsubscribed = 0 AND r.id IS NOT NULL 
                    THEN rg.recipient_id 
                END) as recipient_count
            FROM 
                groups g
                LEFT JOIN recipient_group rg ON g.id = rg.group_id
                LEFT JOIN recipients r ON rg.recipient_id = r.id AND r.deleted_at IS NULL
            WHERE 
                g.user_id = ? AND
                g.deleted_at IS NULL
            GROUP BY 
                g.id, g.name, g.color
            ORDER BY 
                g.name ASC
        ";

        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare fehlgeschlagen: " . $db->error);
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            throw new Exception("Execute fehlgeschlagen: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $groups = [];

        while ($row = $result->fetch_assoc()) {
            $groups[$row['id']] = sprintf(
                '<i class="circle %s icon"></i> %s (%d)',
                htmlspecialchars($row['color']),
                htmlspecialchars($row['name']),
                (int) $row['recipient_count']
            );
        }

        return $groups;

    } catch (Exception $e) {
        error_log("getAllGroups Fehler: " . $e->getMessage());
        return [];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// .env Datei laden
function loadEnvFile($path)
{
    if (file_exists($path)) {
        $envContent = file_get_contents($path);
        $lines = explode("\n", $envContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0)
                continue;

            if (strpos($line, '=') !== false) {
                list($name, $value) = array_map('trim', explode('=', $line, 2));
                if (!empty($name)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
    }
}

// Event-Typ-Definitionen
$eventTypes = [
    'send' => '<i class="paper plane blue icon"></i> Versendet',
    'delivered' => '<i class="check circle green icon"></i> Zugestellt',
    'open' => '<i class="eye blue icon"></i> Geöffnet',
    'click' => '<i class="mouse pointer blue icon"></i> Angeklickt',
    'bounce' => '<i class="exclamation circle red icon"></i> Zurückgewiesen',
    'failed' => '<i class="times circle red icon"></i> Fehlgeschlagen',
    'blocked' => '<i class="ban red icon"></i> Blockiert',
    'spam' => '<i class="warning sign orange icon"></i> Als Spam markiert'
];