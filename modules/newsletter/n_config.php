<?php
//Key für Mailjet wird versentet in send_emails_background.php
$apiKey = '452e5eca1f98da426a9a3542d1726c96';
$apiSecret = '55b277cd54eaa3f1d8188fdc76e06535';

$host = 'localhost';
$username = 'smart';
$password = 'Eiddswwenph21;';
$dbname = 'ssi_newsletter';

$db = $connection = $GLOBALS['mysqli'] = mysqli_connect($host, $username, $password, $dbname);

//Select the database
if (!mysqli_select_db($db, $dbname)) {
    die('Datenbankauswahl fehlgeschlagen: ' . mysqli_error($db));
}

// Funktion zum Abrufen aller Gruppen
function getAllGroups($db)
{
    $groups = [];
    $query = "SELECT id, name FROM groups ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $groups[$row['id']] = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
    }
    return $groups;
}