<?php
header('Content-Type: application/json');
include(__DIR__ . '/../n_config.php');


function getAttachmentInfo($content_id, $uploadBasePath)
{

    $upload_dir = $uploadBasePath . '/' . $content_id . "/";

    $files = glob($upload_dir . "*");
    $count = count($files);
    $total_size = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            $total_size += filesize($file);
        }
    }

    return [
        'count' => $count,
        'size' => round($total_size / 1048576, 2) // Konvertierung zu MB
    ];
}

$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;
echo json_encode(getAttachmentInfo($content_id, $uploadBasePath));