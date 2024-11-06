<?php
include(__DIR__ . '/../n_config.php');

function hasPendingEmails($db)
{
    $query = "SELECT COUNT(*) as count 
              FROM email_jobs ej 
              JOIN email_contents ec ON ej.content_id = ec.id 
              WHERE ec.send_status = 1 
              AND ej.status = 'pending'";

    $result = $db->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    return false;
}

header('Content-Type: application/json');
echo json_encode([
    'hasPendingEmails' => hasPendingEmails($db)
]);

if (isset($db)) {
    $db->close();
}