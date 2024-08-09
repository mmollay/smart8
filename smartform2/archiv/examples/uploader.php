<?php
require_once __DIR__ . '/../FormGenerator.php';
$formGenerator = new FormGenerator();

$submitTarget = 'process_form.php';

$formGenerator->setFormData([
    'id' => 'uploaderForm',
    'action' => $submitTarget,
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "showToast('Dateien erfolgreich hochgeladen', 'success');"
]);

// Uploader
$formGenerator->addField([
    'type' => 'uploader',
    'name' => 'files',
    'config' => array(
        'MAX_FILE_SIZE' => 100 * 1024 * 1024, // 100 MB
        'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024, // 10 GB
        'ALLOWED_FORMATS' => ['pdf', 'jpeg', 'jpg', 'png', 'doc', 'docx'],
        'MAX_FILE_COUNT' => 10,
        'UPLOAD_DIR' => '../uploads/',
        'LANGUAGE' => 'de',
        'dropZoneId' => 'drop-zone',
        'fileInputId' => 'file-input',
        'fileListId' => 'file-list',
        'deleteAllButtonId' => 'delete-all',
        'progressContainerId' => 'progress-container',
        'progressBarId' => 'progress',
        'showDeleteAllButton' => true
    )
]);

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datei-Uploader</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator->generateCSS() ?>
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">Datei-Uploader</h1>
        <?= $formGenerator->generateForm() ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <?= $formGenerator->generateJS() ?>
</body>

</html>