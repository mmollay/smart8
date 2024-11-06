<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'customerForm',
    'action' => 'save/process_customer_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_customer(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
if (isset($_POST['update_id'])) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM customers WHERE customer_id = ?", [$_POST['update_id']]);
} else {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_customer'
    ]);
}

//hidden
$formGenerator->addField([
    'type' => 'hidden',
    'name' => 'customer_id'
]);

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Kontaktdaten',
        '2' => 'Zusätzliche Informationen'
    ],
    'active' => '1'
]);

// Felder für Tab 1: Kontaktdaten
$formGenerator->addField([
    'type' => 'input',
    'name' => 'company_name',
    'label' => 'Firmenname',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'contact_person',
    'label' => 'Ansprechpartner',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'street',
    'label' => 'Straße',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 3,
    'tab' => '1',
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
    'label' => 'Telefon',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'email',
    'label' => 'E-Mail',
    'tab' => '1'
]);

// Felder für Tab 2: Zusätzliche Informationen
$formGenerator->addField([
    'type' => 'input',
    'name' => 'tax_number',
    'label' => 'Steuernummer',
    'tab' => '2'
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