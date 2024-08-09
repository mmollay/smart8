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

    $files = [];

    if (is_dir($uploadDir)) {
        $dirContent = scandir($uploadDir);
        foreach ($dirContent as $file) {
            if ($file !== '.' && $file !== '..') {
                $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($fileType, $allowedTypes)) {
                    $files[] = $uploadDir . $file;
                }
            }
        }
    }

    echo json_encode(['files' => $files]);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}