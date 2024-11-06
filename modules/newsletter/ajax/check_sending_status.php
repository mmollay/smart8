<?php
include(__DIR__ . '/../n_config.php');

$query = "SELECT COUNT(*) as pending_count 
          FROM email_jobs ej 
          JOIN email_contents ec ON ej.content_id = ec.id 
          WHERE ec.send_status = 1 
          AND ej.status = 'pending'";

$result = $db->query($query);
$row = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode([
    'isStillSending' => ($row['pending_count'] > 0)
]);

if (isset($db)) {
    $db->close();
}