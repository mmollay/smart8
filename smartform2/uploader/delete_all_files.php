<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Konfiguration aus POST-Daten oder Standard-Konfiguration verwenden
    $config = isset($_POST['config']) ? json_decode($_POST['config'], true) : require_once 'config.php';

    $uploadDir = $config['UPLOAD_DIR'] ?? '../uploads/';

    // Sicherheitsüberprüfung: Stellen Sie sicher, dass das Verzeichnis existiert und innerhalb des erlaubten Bereichs liegt
    $realUploadDir = realpath($uploadDir);
    if ($realUploadDir === false || !is_dir($realUploadDir)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Upload-Verzeichnis nicht gefunden oder ungültig.'
        ]);
        exit;
    }

    $files = glob($realUploadDir . '/*');
    $errors = [];
    $deletedCount = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $deletedCount++;
            } else {
                $errors[] = basename($file);
            }
        }
    }

    if (empty($errors)) {
        echo json_encode([
            'status' => 'success',
            'message' => "Alle Dateien wurden erfolgreich gelöscht. Insgesamt {$deletedCount} Dateien entfernt."
        ]);
    } else {
        echo json_encode([
            'status' => 'partial',
            'message' => "Einige Dateien konnten nicht gelöscht werden: " . implode(', ', $errors) . ". {$deletedCount} Dateien wurden erfolgreich entfernt."
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ungültige Anfrage-Methode.'
    ]);
}