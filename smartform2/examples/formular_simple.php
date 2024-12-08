<?php
require_once __DIR__ . '/../FormGenerator.php';

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'simpleForm',
    'action' => 'process_form.php',
    'method' => 'POST',
    'class' => 'ui form',
    'responseType' => '',
    'success' => "showToast('Formular erfolgreich gesendet!', 'success');"
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'name',
    'label' => 'Name',
    'placeholder' => 'Ihr Name',
    'value' => 'Max Mustermann'
    // 'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail',
    'placeholder' => 'Ihre E-Mail-Adresse',
    'value' => 'martin@ssi.at'
    // 'required' => true,
    // 'email' => true
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'message',
    'label' => 'Nachricht',
    'placeholder' => 'Ihre Nachricht',
    'required' => true
]);

$formGenerator->addButtonElement([
    [
        'type' => 'submit',
        'name' => 'submit',
        'value' => 'Speichern',
        'icon' => 'save',
        'class' => 'ui primary button'
    ],

]);

// Generiere das Formular
echo $formGenerator->generateForm();
// Generiere den JavaScript-Code
echo $formGenerator->generateJS();