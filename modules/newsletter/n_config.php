<?php
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