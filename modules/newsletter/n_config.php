<?php
// Grundlegende Modul-Konfiguration
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

// Am Anfang von n_config.php
// if (!defined('ALLOW_WEBHOOK') && php_sapi_name() !== 'cli') {
//     // Normale Session-Prüfung nur wenn kein Webhook
//     session_start();
//     if (!isset($_SESSION['user_id'])) {
//         header('Location: /auth/no_access.php');
//         exit;
//     }
// }

if (!$set_unsubscribed) {
    // Config einbinden für nicht-CLI und nicht-Webhook Zugriffe
    if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEBHOOK')) {
        require_once(__DIR__ . '/../../config.php');
    }
}

// Laden der .env Datei
if (file_exists(__DIR__ . '/../../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../../.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// APP_ROOT definieren
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $_ENV['APP_ROOT'] ?? dirname(__DIR__));
}

// Newsletter-spezifische Datenbankkonfiguration
$newsletterDbConfig = [
    'host' => $_ENV['NEWSLETTER_DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['NEWSLETTER_DB_PORT'] ?? 3306,
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

// Hilfsfunktionen für das Newsletter-Modul
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
    $placeholders = array_merge(
        getDefaultPlaceholders($customPlaceholders['email'] ?? null),
        $customPlaceholders
    );

    foreach ($placeholders as $key => $value) {
        $text = str_replace('{{' . $key . '}}', $value, $text);
    }

    return $text;
}

function getAllGroups($db)
{
    global $userId;
    $groups = [];

    $query = "
        SELECT 
            g.id,
            g.name,
            g.color,
            COUNT(DISTINCT CASE WHEN r.unsubscribed = 0 THEN rg.recipient_id END) as recipient_count
        FROM 
            groups g
            LEFT JOIN recipient_group rg ON g.id = rg.group_id
            LEFT JOIN recipients r ON rg.recipient_id = r.id
        WHERE 
            g.user_id = ?
        GROUP BY 
            g.id,
            g.name,
            g.color
        ORDER BY 
            g.name
    ";

    $stmt = $db->prepare($query);
    if (!$stmt) {
        error_log("Prepare fehlgeschlagen: " . $db->error);
        return [];
    }

    // Bind Parameter für bessere Sicherheit
    $stmt->bind_param("s", $userId);

    if (!$stmt->execute()) {
        error_log("Execute fehlgeschlagen: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $groups[$row['id']] = sprintf(
            '<i class="circle %s icon"></i> %s (%d)',
            htmlspecialchars($row['color']),
            htmlspecialchars($row['name']),
            $row['recipient_count']
        );
    }

    $stmt->close();
    return $groups;
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