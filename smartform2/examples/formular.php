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

$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button',
        'tooltip' => 'Klicken Sie hier, um das Formular zu speichern',
        'confirmation' => 'Sind Sie sicher, dass Sie speichern möchten?',
        'disabled' => false,
        'popup' => [
            'content' => 'Speichern Sie Ihre Änderungen',
            'position' => 'top left',
            'variation' => 'large',
            'inverted' => true
        ]
    ],
    [
        'type' => 'button',
        'name' => 'reset',
        'value' => 'Zurücksetzen',
        'icon' => 'undo',
        'class' => 'ui secondary button',
        'onclick' => 'resetForm()',
        'popup' => 'Formular zurücksetzen'
    ],
    [
        'type' => 'button',
        'name' => 'close',
        'value' => 'Schließen',
        'icon' => 'close',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');",
        'negative' => true
    ],
    [
        'type' => 'button',
        'name' => 'custom',
        'value' => '',
        'icon' => 'cog',
        'class' => 'ui icon button',
        'popup' => 'Einstellungen'
    ],
    [
        'type' => 'button',
        'name' => 'help',
        'value' => 'Hilfe',
        'icon' => 'question circle',
        'class' => 'ui blue basic button',
        'labeled' => true,
        'iconPosition' => 'right'
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right',
    'basic' => false,
    'icon' => true,
    'labeled' => false,
    'fluid' => false,
    'compact' => true,
    'toggle' => false,
    'circular' => false,
    'spacing' => '5px'
]);


$fields = [
    [
        'type' => 'input',
        'name' => 'email',
        'label' => 'E-Mail',
        'placeholder' => 'Ihre E-Mail-Adresse',
        'class' => 'required',
        'required' => true,
        'email' => true,
        'width' => 'six',
        'value' => 'martin@ssi.at',
        'split_start' => true,
        'error_message' => 'Bitte füllen Sie dieses Feld aus.',
        'email_error' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
    ],
    [
        'type' => 'input',
        'name' => 'first_name',
        'label' => 'Vorname',
        'placeholder' => 'Ihr Vorname',
        'required' => true,
        'minLength' => 2,
        'value' => 'Max',
        'error_message' => 'Bitte geben Sie Ihren Vornamen ein.',
        'minLength_error' => 'Der Vorname muss mindestens 2 Zeichen lang sein.'
    ],
    [
        'type' => 'input',
        'name' => 'last_name',
        'label' => 'Nachname',
        'placeholder' => 'Ihr Nachname',
        'split_end' => true,
        'required' => true,
        'minLength' => 2,
        'value' => 'Mustermann',
        'error_message' => 'Bitte geben Sie Ihren Nachnamen ein.',
        'minLength_error' => 'Der Nachname muss mindestens 2 Zeichen lang sein.'
    ],

    [
        'type' => 'slider',
        'name' => 'age',
        'label' => 'Alter',
        'max' => 100,
        'step' => 1,
        'value' => 30,
        'number' => true,
        'minValue' => 18,
        'maxValue' => 100,
        'number_error' => 'Bitte geben Sie ein gültiges Alter ein.',
        'minValue_error' => 'Das Mindestalter beträgt 18 Jahre.',
        'maxValue_error' => 'Das maximale Alter beträgt 100 Jahre.'
    ],
    [
        'type' => 'color',
        'name' => 'favorite_color',
        'label' => 'Lieblingsfarbe',
        'value' => '#ff0000'
    ],
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
                    'path' => 'uploads/'
                ]
            ]
        ]
    ]
];


foreach ($fields as $field) {
    $formGenerator->addField($field);
}

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'country',
    'label' => 'Land',
    'array' => array('de' => 'Deutschland', 'at' => 'Österreich', 'ch' => 'Schweiz'),
    'class' => 'required',
    'multiple' => true,
    'required' => true,
    'error_message' => 'Bitte wählen Sie ein Land aus.',
    'placeholder' => '--Land auswählen--',
    'dropdownSettings' => [
        'fullTextSearch' => true,
        'allowAdditions' => false,
        'hideAdditions' => false,
        'clearable' => false,
        'maxSelections' => 2,
        'onChange' => 'function(value, text, $selected) { console.log("Selected: " + value); }',
    ]
]);

// Erweiterter Kalender
$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'event_date',
    'label' => 'Event Datum',
    'placeholder' => 'Wählen Sie ein Datum',
    'class' => 'date-picker',
    'calendarType' => 'date',
    'format' => 'DD.MM.YYYY',
    'minDate' => '2023-01-01',
    'maxDate' => '2023-12-31'
]);


// Standard Checkbox
$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'agree_terms',
    'label' => 'I agree to the terms',
]);

// Toggle Checkbox
$formGenerator->addField([
    'type' => 'checkbox',
    'style' => 'toggle',
    'name' => 'notifications',
    'label' => 'Enable notifications',
]);

// Slider Checkbox
$formGenerator->addField([
    'type' => 'checkbox',
    'style' => 'slider',
    'name' => 'dark_mode',
    'label' => 'Dark Mode',
]);

// Radio-style Checkbox
$formGenerator->addField([
    'type' => 'checkbox',
    'style' => 'radio',
    'name' => 'option_1',
    'label' => 'Option 1',
]);

// Invisible Checkbox
$formGenerator->addField([
    'type' => 'checkbox',
    'style' => 'invisible',
    'name' => 'hidden_option',
    'label' => 'Hidden Option',
]);



$formGenerator->addField([
    'type' => 'radio',
    'name' => 'gender',
    'label' => 'Geschlecht',
    'options' => [
        'male' => 'Männlich',
        'female' => 'Weiblich',
        'other' => 'Divers'
    ],
    'inline' => true, // Optional: für inline-Darstellung
    'class' => 'custom-class' // Optional: zusätzliche CSS-Klasse
]);

$formGenerator->addField([
    'type' => 'grouped_checkbox',
    'name' => 'food_preferences',
    'label' => 'Lebensmittelpräferenzen',
    'options' => [
        'Früchte' => [
            'apple' => 'Apfel',
            'orange' => 'Orange',
            'pear' => 'Birne'
        ],
        'Gemüse' => [
            'lettuce' => 'Salat',
            'carrot' => 'Karotte',
            'spinach' => 'Spinat'
        ]
    ],
    'class' => 'custom-class' // Optional: zusätzliche CSS-Klasse
]);

//nur text
$formGenerator->addField([
    'type' => 'content',
    'value' => 'Hier steht nur Text.',
    'class' => 'ui message'
]);

//uploader
$formGenerator->addField([
    'type' => 'uploader',
    'name' => 'files',
    'config' => array(
        'MAX_FILE_SIZE' => 100 * 1024 * 1024, // 20 MB
        'MAX_FOLDER_SIZE' => 10000 * 1024 * 1024,
        'ALLOWED_FORMATS' => ['pdf', 'jpeg'],
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
    <title>Formular Beispiel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css">
    <?= $formGenerator->generateCSS() ?>
</head>

<body><br><br>

    <div class="ui container">
        <a href="index.php" class="ui button">Zurück</a><br>
        <h1 class="ui header">Beispielformular</h1>
        <?= $formGenerator->generateForm() ?>
        <br><br><br>
    </div>
    <div id=show_data></div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js"></script>
    <?= $formGenerator->generateJS() ?>


</body>

</html>