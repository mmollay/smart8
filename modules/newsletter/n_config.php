<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Key für Mailjet wird versentet in send_emails_background.php
$apiKey = '452e5eca1f98da426a9a3542d1726c96';
$apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

$uploadBasePath = "/Applications/XAMPP/htdocs/smart/smart8/uploads/users/";

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_newsletter';

$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

//Select the database
if (!mysqli_select_db($db, $dbname)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}

// Standardisierte Platzhalter für das gesamte System
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
            g.id, g.name, g.color
        ORDER BY 
            g.name
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $groups[$row['id']] = sprintf(
            '<i class="circle %s icon"></i> %s (%d)',
            htmlspecialchars($row['color']),
            htmlspecialchars($row['name']),
            $row['recipient_count']
        );
    }

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