<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file'])) {
    // Konfiguration aus POST-Daten oder Standard-Konfiguration verwenden
    $config = isset($_POST['config']) ? json_decode($_POST['config'], true) : require_once 'config.php';

    $uploadDir = $config['UPLOAD_DIR'] ?? '../uploads/';
    $file = $uploadDir . basename($_POST['file']);

    // Sicherheitsüberprüfung: Stellen Sie sicher, dass die Datei innerhalb des Upload-Verzeichnisses liegt
    $realUploadDir = realpath($uploadDir);
    $realFile = realpath($file);

    if ($realFile === false || strpos($realFile, $realUploadDir) !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Ungültiger Dateipfad.']);
        exit;
    }

    if (file_exists($file)) {
        if (unlink($file)) {
            echo json_encode(['status' => 'success', 'message' => 'Datei erfolgreich gelöscht.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Fehler beim Löschen der Datei.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datei existiert nicht.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Keine Datei zum Löschen übermittelt.']);
}
?>