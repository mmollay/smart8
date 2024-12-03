<?php
require_once __DIR__ . '/../FormGenerator.php';
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'demoForm',
    'action' => 'process_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'html',
    'success' => "
        function(response) {
            $('#formResponse').html(response).show();
            if(response.includes('success')) {
                showToast('Formular erfolgreich übermittelt', 'success');
            }
            $('html, body').animate({
                scrollTop: $('#formResponse').offset().top
            }, 500);
        }"
]);

// Tabs für bessere Übersichtlichkeit
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        'basis' => 'Basisdaten',
        'erweitert' => 'Erweiterte Eingaben',
        'dokumente' => 'Dokumente & Medien'
    ],
    'active' => 'basis'
]);

// Basisdaten Tab mit Standardwerten
$formGenerator->addFieldGroup('personendaten', [
    [
        'type' => 'input',
        'name' => 'email',
        'label' => 'E-Mail',
        'placeholder' => 'max.mustermann@beispiel.de',
        'required' => true,
        'email' => true,
        'width' => 'eight',
        'value' => 'demo.user@beispiel.de',
        'error_message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
    ],
    [
        'type' => 'input',
        'name' => 'first_name',
        'label' => 'Vorname',
        'placeholder' => 'Max',
        'required' => true,
        'width' => 'four',
        'value' => 'Max'
    ],
    [
        'type' => 'input',
        'name' => 'last_name',
        'label' => 'Nachname',
        'placeholder' => 'Mustermann',
        'required' => true,
        'width' => 'four',
        'value' => 'Mustermann'
    ]
], [
    'title' => 'Persönliche Informationen',
    'wrapper' => 'ui segment'
], 'basis');

// Dropdown und Datum im Basisdaten-Tab
$formGenerator->addFieldGroup('weitere_basis', [
    [
        'type' => 'dropdown',
        'name' => 'country',
        'label' => 'Land',
        'array' => ['de' => 'Deutschland', 'at' => 'Österreich', 'ch' => 'Schweiz'],
        'required' => true,
        'width' => 'eight',
        'multiple' => true,
        'value' => ['at', 'de'],
        'placeholder' => 'Land auswählen',
        'dropdownSettings' => [
            'fullTextSearch' => true,
            'clearable' => true
        ]
    ],
    [
        'type' => 'calendar',
        'name' => 'birthdate',
        'label' => 'Geburtsdatum',
        'width' => 'eight',
        'calendarType' => 'date',
        'format' => 'DD.MM.YYYY'
    ]
], [
    'wrapper' => 'ui segment'
], 'basis');

// Erweiterte Eingaben Tab
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'description',
    'label' => 'Beschreibung',
    'tab' => 'erweitert',
    'config' => [
        'minHeight' => 200,
        'maxHeight' => 400,
        'toolbar' => ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList']
    ]
]);

$formGenerator->addFieldGroup('preferences', [
    [
        'type' => 'checkbox',
        'style' => 'toggle',
        'name' => 'newsletter',
        'label' => 'Newsletter abonnieren',
        'width' => 'eight'
    ],
    [
        'type' => 'checkbox',
        'style' => 'slider',
        'name' => 'darkmode',
        'label' => 'Dark Mode aktivieren',
        'width' => 'eight'
    ]
], [
    'title' => 'Einstellungen',
    'wrapper' => 'ui segment'
], 'erweitert');

$formGenerator->addField([
    'type' => 'grouped_checkbox',
    'name' => 'interests',
    'label' => 'Interessengebiete',
    'tab' => 'erweitert',
    'options' => [
        'Technologie' => [
            'web' => 'Webentwicklung',
            'app' => 'App-Entwicklung',
            'ai' => 'Künstliche Intelligenz'
        ],
        'Design' => [
            'ui' => 'UI Design',
            'ux' => 'UX Design',
            'graphic' => 'Grafikdesign'
        ]
    ]
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




// Aktionsbuttons
$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button',
        'popup' => ['content' => 'Formular speichern']
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
    <title>Formular-Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator->generateCSS() ?>
    <style>
        .ui.container {
            padding: 2em 0;
        }

        .ui.segment {
            margin-top: 1em;
        }

        .ui.form .grouped-checkbox {
            margin-top: 1em;
        }

        .field.width-eight {
            width: 50% !important;
        }

        .ui.header .content {
            padding: 1em 0;
        }
    </style>
</head>


<body>
    <div class="ui container">
        <div class="ui basic segment">
            <h1 class="ui header">
                <i class="wpforms icon"></i>
                <div class="content">
                    Formular-Generator Demo
                    <div class="sub header">Demonstration verschiedener Formularelemente und Funktionen</div>
                </div>
            </h1>

            <div class="ui info message">
                <div class="header">Über diese Demo</div>
                <p>Diese Seite demonstriert die verschiedenen Möglichkeiten des Formular-Generators.
                    Erkunden Sie die verschiedenen Tabs für unterschiedliche Eingabetypen und Funktionen.</p>
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