<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Key für Mailjet wird verwendet in send_emails_background.php
$apiKey = '452e5eca1f98da426a9a3542d1726c96';
$apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

// Prüfe ob Script über CLI oder Web ausgeführt wird
$isCliMode = php_sapi_name() === 'cli';

// Setze Upload-Pfad basierend auf Ausführungsumgebung
if ($isCliMode) {
    $uploadBasePath = "/Applications/XAMPP/htdocs/smart/smart8/uploads/users/";
} else {
    $uploadBasePath = $_SERVER['SERVER_NAME'] === 'localhost'
        ? "/Applications/XAMPP/htdocs/smart/smart8/uploads/users/"
        : "/data/www/develop/uploads/users/";
}

// Datenbank-Konfiguration
$dbConfig = [
    'host' => '127.0.0.1',  // IP statt 'localhost' verwenden
    'port' => 3306,         // Standard MySQL Port
    'username' => 'smart',
    'password' => 'Eiddswwenph21;',
    'dbname' => 'ssi_newsletter'
];

// Verbindung mit Fehlerbehandlung aufbauen
try {
    $mysqli = mysqli_init();

    // Timeout-Einstellungen
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    // Verbindung herstellen
    if (
        !$mysqli->real_connect(
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['dbname'],
            $dbConfig['port']
        )
    ) {
        throw new Exception('Datenbankverbindung fehlgeschlagen: ' . $mysqli->connect_error);
    }

    // Globale Variablen setzen
    $db = $connection = $GLOBALS['mysqli'] = $mysqli;

    // UTF-8 Zeichensatz setzen
    $db->set_charset('utf8mb4');

    // Überprüfe ob die Datenbank ausgewählt werden kann
    if (!$db->select_db($dbConfig['dbname'])) {
        throw new Exception('Datenbankauswahl fehlgeschlagen: ' . $db->error);
    }

} catch (Exception $e) {
    $errorMsg = "Datenbank-Fehler: " . $e->getMessage() . "\n";
    if ($isCliMode) {
        fwrite(STDERR, $errorMsg);
    } else {
        error_log($errorMsg);
    }
    die($errorMsg);
}

// Rest of the existing functions remain the same
function getDefaultPlaceholders($customEmail = null)
{
    $now = new DateTime();
    return [
        // Personendaten
        'vorname' => 'Max',
        'nachname' => 'Mustermann',
        'titel' => 'Dr.',
        'geschlecht' => 'Herr',
        'anrede' => 'Sehr geehrter Herr Dr. Mustermann',

        // Firmendaten
        'firma' => 'Demo GmbH',
        'company' => 'Demo GmbH', // Alias für Abwärtskompatibilität

        // Kontaktdaten
        'email' => $customEmail ?? 'max.mustermann@beispiel.de',

        // Datums- und Zeitangaben (deutsch formatiert)
        'datum' => $now->format('d.m.Y'),
        'datum_lang' => $now->format('l, d. F Y'),
        'uhrzeit' => $now->format('H:i'),
        'uhrzeit_lang' => $now->format('H:i:s'),

        // Zusätzliche Formatierungen
        'datum_kurz' => $now->format('d.m.y'),
        'monat' => $now->format('F'),
        'jahr' => $now->format('Y'),
        'wochentag' => $now->format('l')
    ];
}


// Funktion zum Ersetzen der Platzhalter im Text
function replacePlaceholders($text, $customPlaceholders = [])
{
    // Hole Standardplatzhalter und überschreibe sie mit benutzerdefinierten Werten
    $placeholders = array_merge(
        getDefaultPlaceholders($customPlaceholders['email'] ?? null),
        $customPlaceholders
    );

    // Ersetze alle Platzhalter im Text
    foreach ($placeholders as $key => $value) {
        $text = str_replace('{{' . $key . '}}', $value, $text);
    }

    return $text;
}

function getAllGroups($db)
{
    $groups = [];
    $query = "
        SELECT 
            g.id, 
            g.name, 
            g.color,
            COUNT(DISTINCT rg.recipient_id) as recipient_count
        FROM 
            groups g
            LEFT JOIN recipient_group rg ON g.id = rg.group_id
        GROUP BY 
            g.id, 
            g.name, 
            g.color
        ORDER BY 
            g.name
    ";

    // Fehlerbehandlung für prepare
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        error_log("Prepare failed: " . $db->error);
        return [];  // Leeres Array zurückgeben im Fehlerfall
    }

    // Fehlerbehandlung für execute
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
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

// Filter hinzufügen
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