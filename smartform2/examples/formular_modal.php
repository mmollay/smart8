<?php
require_once '../FormGenerator.php';
$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'myForm',
    'action' => 'process_form.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => '',
    'success' => "showToast('Formular erfolgreich gesendet!', 'success'); $('#myModal').modal('hide');"
]);

$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Personal Information',
        '2' => 'Contact Details',
        '3' => 'Additional Information'
    ],
    'active' => '1'
]);

$formGenerator->addFieldGroup('adresse', [
    ['type' => 'input', 'name' => 'strasse', 'label' => 'Straße', 'required' => true, 'placeholder' => 'Geben Sie Ihre Straße ein'],
    ['type' => 'input', 'name' => 'hausnummer', 'label' => 'Hausnummer'],
    ['type' => 'input', 'name' => 'plz', 'label' => 'PLZ'],
    ['type' => 'input', 'name' => 'ort', 'label' => 'Ort']
], [
    'title' => 'Adressinformationen',
    'wrapper' => 'ui red message'
], '1');

// Felder für den ersten Tab
$formGenerator->addField([
    'type' => 'input',
    'name' => 'vorname',
    'label' => 'Vorname',
    'placeholder' => 'Geben Sie Ihren Vornamen ein',
    'required' => true,
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'nachname',
    'label' => 'Nachname',
    'placeholder' => 'Geben Sie Ihren Nachnamen ein',
    'required' => true,
    'tab' => '1'
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
        'showDeleteAllButton' => true,
        'tab' => '1'
    )
]);

// Felder für den zweiten Tab
$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail',
    'placeholder' => 'Geben Sie Ihre E-Mail-Adresse ein',
    'required' => true,
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'telefon',
    'label' => 'Telefon',
    'placeholder' => 'Geben Sie Ihre Telefonnummer ein',
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 3,
    'tab' => '2',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'postal_code',
            'label' => 'PLZ',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'city',
            'label' => 'Stadt',
            'width' => 1
        ],
        [
            'type' => 'input',
            'name' => 'country',
            'label' => 'Land',
            'width' => 1
        ]
    ]
]);

// Felder für den dritten Tab
$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'text',
    'label' => 'Text',
    'placeholder' => 'Geben Sie Ihren Text ein',
    'required' => true,
    'config' => [
        'minHeight' => 200,
        'maxHeight' => 400
    ],
    'tab' => '3'
]);

$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],
    [
        'name' => 'close',
        'value' => 'Schließen',
        'icon' => 'close',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

// Generiere das Formular
echo $formGenerator->generateForm();

// Generiere den JavaScript-Code
echo $formGenerator->generateJS();

?>