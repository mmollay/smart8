<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'expenseForm',
    'action' => 'save/process_expense_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_expense(response);"
]);

// Bestimmen des Modus (Bearbeiten oder Neu)
$update_id = $_POST['update_id'] ?? null;

if ($update_id) {
    $formGenerator->loadValuesFromDatabase($GLOBALS['db'], "SELECT * FROM expenses WHERE expense_id = ?", [$update_id]);
} else {
    $formGenerator->addField([
        'type' => 'hidden',
        'name' => 'modus',
        'value' => 'add_expense'
    ]);
    // Setze Standardwerte für neue Ausgaben
    $expense_date = date('Y-m-d');
    $expense_number = getNextExpenseNumber($GLOBALS['db']);
}

//hidden


// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Allgemeine Informationen',
        '2' => 'Ausgabenpositionen'
    ],
    'active' => '1'
]);

// Tab 1: Allgemeine Informationen
$formGenerator->addField([
    'type' => 'input',
    'name' => 'expense_number',
    'label' => 'Ausgabennummer',
    'required' => true,
    'value' => $expense_number ?? '',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'supplier_id',
    'label' => 'Lieferant',
    'array' => getSupplierArray($GLOBALS['db']),
    'required' => true,
    'class' => 'search',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'expense_date',
    'label' => 'Ausgabendatum',
    'required' => true,
    'value' => $expense_date ?? '',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'calendar',
    'name' => 'due_date',
    'label' => 'Fälligkeitsdatum',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'input',
    'name' => 'total_amount',
    'label' => 'Gesamtbetrag',
    'required' => true,
    'format' => 'euro',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'paid',
    'label' => 'Bezahlt',
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'textarea',
    'name' => 'description',
    'label' => 'Beschreibung',
    'tab' => '1'
]);

// Tab 2: Ausgabenpositionen
$formGenerator->addField([
    'type' => 'dropdown',
    'array' => getAccountArray($GLOBALS['db']),
    'name' => 'expense_items',
    'label' => 'Ausgabenpositionen',
    'columns' => [
        'description' => 'Beschreibung',
        'quantity' => 'Menge',
        'unit_price' => 'Einzelpreis',
        'total_price' => 'Gesamtpreis',
        'account_id' => 'Konto'
    ],
    'tab' => '1'
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

// Hilfsfunktionen
function getNextExpenseNumber($db)
{
    $query = "SELECT MAX(CAST(SUBSTRING(expense_number, 5) AS UNSIGNED)) as max_number 
              FROM expenses 
              WHERE expense_number LIKE CONCAT(YEAR(CURDATE()), '-%')";
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $nextNumber = ($row['max_number'] ?? 0) + 1;
    return date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

function getSupplierArray($db)
{
    $suppliers = [];
    $query = "SELECT supplier_id, company_name FROM suppliers ORDER BY company_name";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $suppliers[$row['supplier_id']] = $row['company_name'];
    }

    return $suppliers;
}

function getAccountArray($db)
{
    $accounts = [];
    $query = "SELECT account_id, CONCAT(account_name, ' (', IFNULL(percentage, ''), '%)') AS account_display 
              FROM accounts 
              ORDER BY account_name";
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $accounts[$row['account_id']] = $row['account_display'];
    }
    return $accounts;
}