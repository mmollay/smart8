<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// echo "post_max_size: " . ini_get('post_max_size') . "\n";
// echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
// echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";

// var_dump($_POST);
// var_dump($_FILES);

// Funktion zum sicheren Lesen von JSON-Daten
function safeJsonDecode($json, $defaultValue = [])
{
    $data = json_decode($json, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : $defaultValue;
}

// Logging-Funktion
function logData($message)
{
    $logFile = __DIR__ . '/upload_log.txt';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Log alle POST-Daten
logData("POST data: " . print_r($_POST, true));

// Log alle FILES-Daten
logData("FILES data: " . print_r($_FILES, true));

$ckEditorConfig = isset($_POST['ckEditorConfig']) ? safeJsonDecode($_POST['ckEditorConfig'], []) : [];
error_log("Received CKEditor config: " . print_r($ckEditorConfig, true));

// Konfigurierbare Variablen, die die CKEditor-Einstellungen berücksichtigen
$MAX_FILE_SIZE = $ckEditorConfig['maxFileSize'] ?? 5 * 1024 * 1024; // Default 5MB
$ALLOWED_EXTENSIONS = $ckEditorConfig['types'] ?? ['jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'];
$ALLOWED_MIME_TYPES = array_map(function ($ext) {
    return 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
}, $ALLOWED_EXTENSIONS);


// Basis-Upload-Ordner
$base_upload_dir = __DIR__ . '/' . ($ckEditorConfig['path'] ?? 'uploads/');
logData("Base upload directory: " . $base_upload_dir);

// Stelle sicher, dass der Upload-Ordner existiert
if (!file_exists($base_upload_dir)) {
    if (!mkdir($base_upload_dir, 0777, true)) {
        $error_message = "Failed to create upload directory: $base_upload_dir";
        logData($error_message);
        echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
        exit;
    }
}

if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['upload']['name'];
    $file_tmp_name = $_FILES['upload']['tmp_name'];
    $file_size = $_FILES['upload']['size'];

    logData("File received: $file_name");

    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($file_ext, $ALLOWED_EXTENSIONS)) {
        if ($file_size > $MAX_FILE_SIZE) {
            $error_message = "File too large: $file_size bytes";
            logData($error_message);
            echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
            exit;
        }

        $new_file_name = bin2hex(random_bytes(16)) . '.' . $file_ext;
        $upload_path = $base_upload_dir . $new_file_name;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp_name);
        finfo_close($finfo);

        if (!in_array($mime_type, $ALLOWED_MIME_TYPES)) {
            $error_message = "Invalid MIME type: $mime_type";
            logData($error_message);
            echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
            exit;
        }

        if (move_uploaded_file($file_tmp_name, $upload_path)) {
            logData("File uploaded successfully: $upload_path");
            $webroot = $_SERVER['DOCUMENT_ROOT'];
            $relative_path = str_replace($webroot, '', $upload_path);
            $url = $relative_path;

            echo json_encode(['uploaded' => 1, 'fileName' => $new_file_name, 'url' => $url]);
        } else {
            $error_message = "Failed to move uploaded file";
            logData($error_message);
            echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
        }
    } else {
        $error_message = "Invalid file type: $file_ext";
        logData($error_message);
        echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
    }
} else {
    $error_message = "File upload error or no file uploaded. ";
    if (isset($_FILES['upload'])) {
        $error_message .= "Error code: " . $_FILES['upload']['error'];
    } else {
        $error_message .= "No 'upload' key in \$_FILES.";
    }
    logData($error_message);
    echo json_encode(['uploaded' => 0, 'error' => ['message' => $error_message]]);
}