<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = json_decode($_POST['config'], true);

    if (!$config) {
        echo json_encode(['error' => 'Invalid configuration']);
        exit;
    }

    $uploadDir = $config['UPLOAD_DIR'];
    $allowedTypes = $config['ALLOWED_FORMATS'];

    if (!isset($_POST['file'])) {
        echo json_encode(['error' => 'No file specified']);
        exit;
    }

    $filename = basename($_POST['file']);
    $file = $uploadDir . $filename;

    if (!file_exists($file)) {
        echo json_encode(['error' => 'File not found']);
        exit;
    }

    $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }

    $size = filesize($file);
    echo json_encode(['size' => $size]);

} else {
    echo json_encode(['error' => 'Invalid request method']);
}