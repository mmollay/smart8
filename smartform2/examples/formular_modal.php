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
    'type' => 'input',
    'name' => 'vorname',
    'label' => 'Vorname',
    'placeholder' => 'Geben Sie Ihren Vornamen ein',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'nachname',
    'label' => 'Nachname',
    'placeholder' => 'Geben Sie Ihren Nachnamen ein',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'ckeditor5',
    'name' => 'text',
    'label' => 'Text',
    'placeholder' => 'Geben Sie Ihren Text ein',
    'required' => true,
    'config' => [
        'minHeight' => 200,
        'maxHeight' => 400
    ]
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