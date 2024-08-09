<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['files'])) {
    $files = $_POST['files'];
    file_put_contents('files.json', json_encode($files));
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Keine Dateien zum Speichern übermittelt.']);
}
?>