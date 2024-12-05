<?php
require_once __DIR__ . '/../FormGenerator.php';
$formGenerator = new FormGenerator();

// Formular-Konfiguration
$formGenerator->setFormData([
    'id' => 'editorForm',
    'action' => 'process_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "showToast('Formular erfolgreich gespeichert', 'success');"
]);

// Gemeinsame Editor-Konfiguration
$defaultEditorConfig = [
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
    'language' => 'de',
    'placeholder' => 'Geben Sie hier Ihren Text ein...'
];

// Haupt-Editor mit Bildupload
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'mainContent',
    'label' => 'Hauptinhalt',
    'value' => '',
    'required' => true,
    'minLength' => 10,
    'config' => array_merge($defaultEditorConfig, [
        'minHeight' => 300,
        'maxHeight' => 500,
        'toolbar' => array_merge($defaultEditorConfig['toolbar']['items'], ['imageUpload']),
        'image' => [
            'upload' => [
                'types' => ['jpeg', 'png', 'gif'],
                'maxFileSize' => 5 * 1024 * 1024,
                'path' => '../../test/'
            ]
        ]
    ])
]);

// Zusätzlicher Editor für Kurztext
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'shortContent',
    'label' => 'Kurzbeschreibung',
    'required' => true,
    'config' => array_merge($defaultEditorConfig, [
        'minHeight' => 150,
        'maxHeight' => 300
    ])
]);

//uploader
$formGenerator->addField([
    'type' => 'uploader',
    'name' => 'files',
    'tab' => 'dokumente',
    'config' => array(
        'MAX_FILE_SIZE' => 100 * 1024 * 1024, // 20 MB
        'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,
        'ALLOWED_FORMATS' => ['pdf', 'jpeg'],
        'MAX_FILE_COUNT' => 10,
        'UPLOAD_DIR' => '../uploads/',
        'basePath' => 'uploads/',
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

// Formular-Buttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],
    [
        'type' => 'button',
        'name' => 'reset',
        'value' => 'Zurücksetzen',
        'icon' => 'undo',
        'class' => 'ui secondary button',
        'onclick' => 'resetForm()'
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Text Editor Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator->generateCSS() ?>
    <style>
        .ui.container {
            padding: 2em 0;
        }

        .editor-container {
            margin-bottom: 2em;
        }

        .ui.segment {
            box-shadow: none;
        }
    </style>
</head>

<body>
    <div class="ui container">
        <div class="ui segment">
            <h1 class="ui header">
                <i class="edit outline icon"></i>
                <div class="content">
                    Rich Text Editor Demo
                    <div class="sub header">Demonstration der CKEditor-Integration mit Datei-Upload</div>
                </div>
            </h1>

            <div class="ui info message">
                <div class="header">Über diese Demo</div>
                <p>Diese Seite demonstriert die Integration des CKEditor 5 mit verschiedenen Konfigurationen.
                    Der Haupteditor unterstützt Bildupload, während der zweite Editor für einfachere Texteingaben
                    optimiert ist.</p>
            </div>

            <?= $formGenerator->generateForm() ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js"></script>
    <script src="../js/fileUploader.js"></script>
    <script src="../js/formGenerator.js"></script>
    <script src="../js/ckeditor-init.js"></script>
    <?= $formGenerator->generateJS() ?>
</body>

</html>