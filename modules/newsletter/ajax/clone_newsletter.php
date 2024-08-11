<?php
include (__DIR__ . '/../n_config.php');
header('Content-Type: application/json');

function sendJsonResponse($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if (!isset($_POST['content_id'])) {
    sendJsonResponse('error', 'Keine content_id übergeben.');
}

$content_id = intval($_POST['content_id']);

try {
    $db->begin_transaction();

    // Daten des Originals abrufen
    $stmt = $db->prepare("SELECT sender_id, subject, message FROM email_contents WHERE id = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $original = $result->fetch_assoc();
    $stmt->close();

    if (!$original) {
        throw new Exception('Original-Newsletter nicht gefunden.');
    }

    // Neuen Newsletter erstellen
    $new_subject = "Kopie von: " . $original['subject'];
    $stmt = $db->prepare("INSERT INTO email_contents (sender_id, subject, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $original['sender_id'], $new_subject, $original['message']);
    $stmt->execute();
    $new_content_id = $db->insert_id;
    $stmt->close();

    // Gruppen kopieren
    $stmt = $db->prepare("INSERT INTO email_content_groups (email_content_id, group_id) SELECT ?, group_id FROM email_content_groups WHERE email_content_id = ?");
    $stmt->bind_param("ii", $new_content_id, $content_id);
    $stmt->execute();
    $stmt->close();

    $db->commit();
    sendJsonResponse('success', 'Newsletter erfolgreich dupliziert.');

} catch (Exception $e) {
    $db->rollback();
    sendJsonResponse('error', $e->getMessage());
} finally {
    $db->close();
}