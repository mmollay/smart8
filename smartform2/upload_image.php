<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$ckEditorConfigRaw = $_POST['ckEditorConfig'] ?? '{}';
$firstBrace = strpos($ckEditorConfigRaw, '{');
$lastBrace = strpos($ckEditorConfigRaw, '}') + 1;
$cleanConfig = substr($ckEditorConfigRaw, $firstBrace, $lastBrace - $firstBrace);

$ckEditorConfig = safeJsonDecode($cleanConfig);
$uploadConfig = $ckEditorConfig ?? [];
$uploadPath = $uploadConfig['path'] ?? '../uploads/';

// Base upload directory ohne JSON-Response
//$base_upload_dir = __DIR__ . '/' . $uploadPath;
$base_upload_dir = $_SERVER['DOCUMENT_ROOT'] . $uploadPath;
// Upload-Einstellungen
$MAX_FILE_SIZE = $uploadConfig['maxFileSize'] ?? 5 * 1024 * 1024;
$ALLOWED_EXTENSIONS = $uploadConfig['types'] ?? ['jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];
$ALLOWED_MIME_TYPES = array_map(fn($ext) => 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext), $ALLOWED_EXTENSIONS);

// Verzeichnis erstellen falls nicht vorhanden
if (!file_exists($base_upload_dir) && !mkdir($base_upload_dir, 0777, true)) {
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "Failed to create upload directory"]]);
    exit;
}

// Datei-Upload verarbeiten
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['upload']) ? $_FILES['upload']['error'] : 'No upload file found';
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "Upload error: $error"]]);
    exit;
}

$file = $_FILES['upload'];
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Validierungen
if (!in_array($file_ext, $ALLOWED_EXTENSIONS)) {
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "Invalid file type: $file_ext"]]);
    exit;
}

if ($file['size'] > $MAX_FILE_SIZE) {
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "File too large"]]);
    exit;
}

$mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
if (!in_array($mime_type, $ALLOWED_MIME_TYPES)) {
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "Invalid MIME type"]]);
    exit;
}

// Datei speichern
$new_file_name = bin2hex(random_bytes(16)) . '.' . $file_ext;
$upload_path = $base_upload_dir . $new_file_name;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['uploaded' => 0, 'error' => ['message' => "Failed to move uploaded file"]]);
    exit;
}

// Erfolgreicher Upload
$relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $upload_path);
echo json_encode([
    'uploaded' => 1,
    'fileName' => $new_file_name,
    'url' => $relative_path
]);


function safeJsonDecode($json, $defaultValue = [])
{
    $data = json_decode($json, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : $defaultValue;
}

function logData($message)
{
    file_put_contents(
        __DIR__ . '/upload_log.txt',
        date('[Y-m-d H:i:s] ') . $message . PHP_EOL,
        FILE_APPEND
    );
}