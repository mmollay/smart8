<?php
include (__DIR__ . '/../n_config.php');

//header('Content-Type: application/json');

// Überprüfen, ob die content_id übergeben wurde
if (!isset($_POST['content_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Keine content_id übergeben.']);
    exit;
}

$content_id = intval($_POST['content_id']);

// Überprüfen, ob der Newsletter bereits gesendet wird
$sql = "SELECT send_status FROM email_contents WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$stmt->bind_result($send_status);
$stmt->fetch();
$stmt->close();

if ($send_status != 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dieser Newsletter wird bereits gesendet.']);
    exit;
}

// Setze den send_status auf 1
$sql = "UPDATE email_contents SET send_status = 1 WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$stmt->close();

// Füge die Empfänger aus den Gruppen in die email_jobs-Tabelle ein
$sql = "
    INSERT INTO email_jobs (content_id, sender_id, recipient_id, status)
    SELECT ec.id, ec.sender_id, rg.recipient_id, 'pending'
    FROM email_contents ec
    JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
    JOIN recipient_group rg ON ecg.group_id = rg.group_id
    WHERE ec.id = ?
    AND NOT EXISTS (
        SELECT 1 FROM email_jobs ej
        WHERE ej.content_id = ec.id
        AND ej.sender_id = ec.sender_id
        AND ej.recipient_id = rg.recipient_id
    )
";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$stmt->close();

// Überprüfen, ob neue Jobs hinzugefügt wurden
$sql = "SELECT id FROM email_jobs WHERE content_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result();
$job_ids = [];
while ($row = $result->fetch_assoc()) {
    $job_ids[] = $row['id'];
}
$stmt->close();

if (empty($job_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Keine neuen Jobs zum Senden gefunden.']);
    exit;
}

// Log-Einträge für den Start des Versandprozesses
foreach ($job_ids as $job_id) {
    $sql = "INSERT INTO email_logs (job_id, status, response) VALUES (?, 'send', 'Versand gestartet')";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $job_id);
    if (!$stmt->execute()) {
        // Fehlermeldung abfangen und ausgeben
        echo json_encode(['status' => 'error', 'message' => 'Fehler beim Einfügen in die Logs: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
    $stmt->close();
}

echo json_encode(['status' => 'success', 'message' => 'Newsletter wird gesendet.']);
?>