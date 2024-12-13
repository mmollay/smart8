<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../n_config.php';

// Input-Validierung
if (!isset($_POST['content_id']) || !isset($_POST['type'])) {
    die(json_encode(['success' => false, 'message' => 'Fehlende Parameter']));
}

$contentId = intval($_POST['content_id']);
$type = $_POST['type'];

// Typ-Validierung
if (!in_array($type, ['sent', 'opened', 'clicked'])) {
    die(json_encode(['success' => false, 'message' => 'Ungültiger Typ']));
}

$response = ['success' => false];

try {
    // Empfänger basierend auf Typ ermitteln
    $sql = "SELECT DISTINCT r.id 
            FROM recipients r
            JOIN email_jobs ej ON r.id = ej.recipient_id ";

    switch ($type) {
        case 'sent':
            $sql .= "WHERE ej.content_id = ? AND ej.status = 'send'";
            break;
        case 'opened':
            // Für geöffnete Mails
            $sql .= "WHERE ej.content_id = ? AND ej.status = 'open'";
            break;
        case 'clicked':
            // Für geklickte Mails
            $sql .= "WHERE ej.content_id = ? AND ej.status = 'click'";
            break;
    }

    // Zusätzliche Sicherheitsprüfung für user_id
    $sql .= " AND r.user_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('ii', $contentId, $userId);

    if (!$stmt->execute()) {
        throw new Exception("Fehler beim Laden der Empfänger");
    }

    $result = $stmt->get_result();
    $recipientIds = [];

    while ($row = $result->fetch_assoc()) {
        $recipientIds[] = $row['id'];
    }

    if (empty($recipientIds)) {
        die(json_encode(['success' => false, 'message' => 'Keine Empfänger gefunden']));
    }

    // Transaktion starten
    $db->begin_transaction();

    try {
        // Temporäre Gruppe erstellen
        $groupName = "Temp: " . ucfirst($type) . " (NL #" . $contentId . ")";
        $description = "Automatisch erstellt für Newsletter #$contentId - " . ucfirst($type) . " Filter";

        $sql = "INSERT INTO groups (name, user_id, description, color) 
                VALUES (?, ?, ?, 'orange')";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('sis', $groupName, $userId, $description);

        if (!$stmt->execute()) {
            throw new Exception("Fehler beim Erstellen der Gruppe");
        }

        $groupId = $db->insert_id;

        // Empfänger zur Gruppe hinzufügen
        $values = [];
        foreach ($recipientIds as $rid) {
            $values[] = "(" . intval($rid) . ", " . $groupId . ")";
        }

        $sql = "INSERT INTO recipient_group (recipient_id, group_id) 
                VALUES " . implode(',', $values);

        if (!$db->query($sql)) {
            throw new Exception("Fehler beim Hinzufügen der Empfänger");
        }

        // Transaktion bestätigen
        $db->commit();

        $response = [
            'success' => true,
            'group_id' => $groupId,
            'recipient_count' => count($recipientIds),
            'group_name' => $groupName,
            'type' => $type
        ];

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

$db->close();
exit(json_encode($response));