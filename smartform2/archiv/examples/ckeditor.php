<?php
require_once __DIR__ . '/../FormGenerator.php';
$formGenerator = new FormGenerator();

$submitTarget = 'process_form.php';

$formGenerator->setFormData([
    'id' => 'myForm',
    'action' => $submitTarget,
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'normal',
    'success' => "$('#show_data').html(response); showToast('Erfolgreich versendet', 'success');"
]);

$fields = [
    [
        'type' => 'ckeditor5',
        'name' => 'description',
        'label' => 'Beschreibung',
        'value' => 'Hier Ihre Beschreibung eingeben...',
        'required' => true,
        'minLength' => 10,
        'maxLength' => 1000,
        'error_message' => 'Bitte geben Sie eine Beschreibung ein.',
        'minLength_error' => 'Die Beschreibung muss mindestens 10 Zeichen lang sein.',
        'maxLength_error' => 'Die Beschreibung darf höchstens 1000 Zeichen lang sein.',
        'config' => [
            'minHeight' => 300,
            'maxHeight' => 300,
            'toolbar' => [
                'items' => [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'imageUpload',
                    'blockQuote',
                    'insertTable',
                    'undo',
                    'redo'
                ]
            ],
            'placeholder' => 'Geben Sie hier Ihre Beschreibung ein...',
            'image' => [
                'upload' => [
                    'types' => ['jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff'],
                    'maxFileSize' => 5 * 1024 * 1024, // 5 MB
                    'path' => '../uploads/'
                ]
            ]
        ]
    ]
];

//nochmal einen Editor
$fields[] = [
    'type' => 'ckeditor5',
    'name' => 'description2',
    'label' => 'Beschreibung 2',
    'value' => 'Hier Ihre Beschreibung eingeben...',
    'required' => true,
    'minLength' => 10,
    'maxLength' => 1000,
    'error_message' => 'Bitte geben Sie eine Beschreibung ein.',
    'minLength_error' => 'Die Beschreibung muss mindestens 10 Zeichen lang sein.',
    'maxLength_error' => 'Die Beschreibung darf höchstens 1000 Zeichen lang sein.',
    'config' => [
        'minHeight' => 100,
        'maxHeight' => 300,
        'toolbar' => [
            'items' => [
                'heading',
                '|',
                'bold',
                'italic',
                'link',
                'bulletedList',
                'numberedList',
                '|',

                'blockQuote',
                'insertTable',
                'undo',
                'redo'
            ]
        ],
        'placeholder' => 'Geben Sie hier Ihre Beschreibung ein...',
    ]
];

foreach ($fields as $field) {
    $formGenerator->addField($field);
}

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

// Submit-Button
$formGenerator->addField([
    'type' => 'button',
    'buttonType' => 'submit',
    'name' => 'submit',
    'value' => 'Absenden',
    'icon' => 'paper plane',
    'color' => 'primary',
]);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CKEditor Beispiel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator->generateCSS() ?>
</head>

<body>
    <div class="ui container" style="padding-top: 50px;">
        <h1 class="ui header">CKEditor Beispiel</h1>
        <?= $formGenerator->generateForm() ?>
    </div>
    <div id=show_data></div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js"></script>
    <?= $formGenerator->generateJS() ?>
</body>

</html>