<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'accountForm',
    'action' => 'save/process_account_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_account(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
$update_id = $_POST['update_id'] ?? null;

if ($update_id) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM accounts WHERE account_id = ?", [$update_id]);
} else {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_account'
    ]);
}

//hidden
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'account_id'
]);

// Kontodaten
$formGenerator->addField([
    'type' => 'input',
    'name' => 'account_number',
    'label' => 'Kontonummer',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'account_name',
    'label' => 'Kontoname',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'account_type',
    'label' => 'Kontotyp',
    'array' => [
        'Income' => 'Einnahmen',
        'Expense' => 'Ausgaben',
        'Bank' => 'Bank'
    ],
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'percentage',
    'label' => 'Prozentsatz',
    'format' => 'number',
    'step' => '0.01',
    'min' => '0',
    'max' => '100',
    'placeholder' => 'Optional'
]);

// Optional: Zusätzliche Felder, die für Ihre spezifischen Anforderungen relevant sein könnten
$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'placeholder' => 'Optionale Beschreibung des Kontos'
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