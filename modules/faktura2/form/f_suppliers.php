<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'supplierForm',
    'action' => 'save/process_supplier_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_supplier(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
$update_id = $_POST['update_id'] ?? null;

if ($update_id) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM suppliers WHERE supplier_id = ?", [$update_id]);
} else {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_supplier'
    ]);
}

//hidden
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'supplier_id'
]);

// Lieferantendaten
$formGenerator->addField([
    'type' => 'input',
    'name' => 'company_name',
    'label' => 'Firmenname',
    'required' => true
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'contact_person',
    'label' => 'Ansprechpartner'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'street',
    'label' => 'Straße'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 3,
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

$formGenerator->addField([
    'type' => 'input',
    'name' => 'phone',
    'label' => 'Telefon'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail',
    'format' => 'email'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'tax_number',
    'label' => 'Steuernummer'
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