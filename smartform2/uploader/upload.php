<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Konfiguration aus POST-Daten oder Standard-Konfiguration verwenden
    $config = isset($_POST['config']) ? json_decode($_POST['config'], true) : require_once 'config.php';
    $uploadDir = $config['UPLOAD_DIR'] ?? '../uploads/';
    $allowedTypes = $config['ALLOWED_FORMATS'] ?? ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = $config['MAX_FILE_SIZE'] ?? 5 * 1024 * 1024; // 5MB default
    $maxFolderSize = $config['MAX_FOLDER_SIZE'] ?? 100 * 1024 * 1024; // 100MB default

    // Überprüfen und erstellen des Upload-Verzeichnisses, falls es nicht existiert
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Konnte Upload-Verzeichnis nicht erstellen.']);
            exit;
        }
    }

    $fileName = basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Überprüfen der Dateigröße
    if ($_FILES['file']['size'] > $maxFileSize) {
        echo json_encode(['status' => 'error', 'message' => 'Die Datei ist zu groß.']);
        exit;
    }

    // Überprüfen des Dateityps
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Dieser Dateityp ist nicht erlaubt.']);
        exit;
    }

    // Überprüfen Sie die aktuelle Ordnergröße
    $currentFolderSize = array_sum(array_map('filesize', glob($uploadDir . "*")));
    if ($currentFolderSize + $_FILES['file']['size'] > $maxFolderSize) {
        echo json_encode(['status' => 'error', 'message' => 'Maximale Ordnergröße überschritten.']);
        exit;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Die Datei existiert bereits.']);
        exit;
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo json_encode(['status' => 'success', 'message' => 'Datei erfolgreich hochgeladen.', 'file' => $targetFile]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Fehler beim Hochladen der Datei.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Keine Datei hochgeladen.']);
}
?>