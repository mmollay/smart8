<?php
include(__DIR__ . '/../../n_config.php');

header('Content-Type: application/json');

try {
    $sql = "SELECT id, name, description FROM email_templates ORDER BY name ASC";
    $result = $db->query($sql);

    $templates = [];
    while ($row = $result->fetch_assoc()) {
        $templates[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description']
        ];
    }

    echo json_encode([
        'success' => true,
        'templates' => $templates
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden der Templates: ' . $e->getMessage()
    ]);
}

$db->close();

