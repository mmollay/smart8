<?php
require_once(__DIR__ . '/../n_config.php');
require_once(__DIR__ . '/../classes/importers/RecipientImporter.php');

header('Content-Type: application/json');

try {
    if (!isset($_FILES['importFile'])) {
        throw new Exception('Keine Datei hochgeladen');
    }

    $importer = new RecipientImporter($db);

    // Verarbeite die Gruppen-IDs
    $group_ids = [];
    if (isset($_POST['group_ids']) && is_array($_POST['group_ids'])) {
        $group_ids = array_map('intval', $_POST['group_ids']);
    }

    // Überprüfe den Status der Überschreiben-Checkbox
    $overwriteExisting = isset($_POST['overwriteExisting']) && $_POST['overwriteExisting'] === 'on';

    $result = $importer->processImport(
        $_FILES['importFile'],
        $group_ids,
        isset($_POST['skipHeader']) && $_POST['skipHeader'] === 'on',
        $_POST['delimiter'] ?? ',',
        $overwriteExisting  // Neuer Parameter
    );

    echo json_encode($result);

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