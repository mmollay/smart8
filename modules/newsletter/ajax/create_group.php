<?php
// ajax/create_group.php
require_once(__DIR__ . '/../n_config.php');

header('Content-Type: application/json');

try {
    // Validiere Eingaben
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        throw new Exception('Gruppenname ist erforderlich');
    }

    if (empty($color)) {
        throw new Exception('Farbe ist erforderlich');
    }

    // Validiere Farbe gegen erlaubte Werte
    $allowedColors = [
        'red',
        'orange',
        'yellow',
        'olive',
        'green',
        'teal',
        'blue',
        'violet',
        'purple',
        'pink',
        'brown',
        'grey'
    ];
    if (!in_array($color, $allowedColors)) {
        throw new Exception('Ungültige Farbe ausgewählt');
    }

    // Prüfe ob Gruppe bereits existiert
    $stmt = $db->prepare("SELECT id FROM `groups` WHERE name = ? AND user_id = ?");
    $stmt->bind_param('si', $name, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Eine Gruppe mit diesem Namen existiert bereits');
    }
    $stmt->close();

    // Erstelle neue Gruppe
    $stmt = $db->prepare("INSERT INTO `groups` (user_id, name, color, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $userId, $name, $color, $description);

    if (!$stmt->execute()) {
        throw new Exception('Fehler beim Speichern der Gruppe: ' . $db->error);
    }

    $groupId = $db->insert_id;
    $stmt->close();

    // Erfolgreiche Antwort
    echo json_encode([
        'success' => true,
        'id' => $groupId,
        'name' => $name,
        'color' => $color,
        'message' => 'Gruppe erfolgreich erstellt'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($db)) {
    $db->close();
}
?>