<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');


$formGenerator = new FormGenerator();
$formGenerator->setFormData([
    'id' => 'articleForm',
    'action' => 'save/process_article_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_article(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
$update_id = $_POST['update_id'] ?? null;

if ($update_id) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM articles WHERE article_id = ?", [$update_id]);
} else {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_article'
    ]);
}

//hidden
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'article_id'
]);

// Artikeldaten
$formGenerator->addField([
    'type' => 'input',
    'name' => 'article_number',
    'label' => 'Artikelnummer',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'name',
    'label' => 'Artikelname',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'unit',
    'label' => 'Einheit',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'price',
    'label' => 'Preis',
    'required' => true,
    'format' => 'euro'
]);

// Dropdown für Konten hinzufügen
$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'account_id',
    'label' => 'Konto',
    'required' => true,
    'array' => getAccountArray($GLOBALS['db']),
    'class' => 'search'
]);

// Buttons
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
        'name' => 'cancel',
        'value' => 'Abbrechen',
        'icon' => 'cancel',
        'class' => 'ui button',
        'onclick' => "$('.ui.modal').modal('hide');"
    ]
], [
    'layout' => 'grouped',
    'alignment' => 'right'
]);

echo $formGenerator->generateJS();
echo $formGenerator->generateForm();