<?php
include(__DIR__ . '/../f_config.php');
include(__DIR__ . '/../../../smartform2/FormGenerator.php');

$formGenerator = new FormGenerator();

$formGenerator->setFormData([
    'id' => 'billForm',
    'action' => 'process_bill_form.php',
    'class' => 'ui form',
    'method' => 'POST',
    'responseType' => 'json',
    'success' => "after_form_bill(response);"
]);

// Bestimmen des Modus (Bearbeiten, Klonen oder Neu)
$clone = $_GET['clone'] ?? false;
$update_id = $_POST['update_id'] ?? null;
$document = $_GET['document'] ?? null;

if (!$update_id || $clone) {
    $company_id = $default_company_id = $_SESSION['faktura_company_id'];
    $year = date('Y');
    $date_create = date("Y-m-d");
    $bill_number = getNextBillNumber($GLOBALS['mysqli'], $year, $document, $company_id);
} else {
    $formGenerator->loadValuesFromDatabase($GLOBALS['mysqli'], "SELECT * FROM bills WHERE bill_id = ?", [$update_id]);
}

// Tabs definieren
$formGenerator->addField([
    'type' => 'tab',
    'tabs' => [
        '1' => 'Step 1 (Empfänger)',
        '2' => 'Step 2 (Positionen)',
        '3' => 'Step 3 (Zusatz)'
    ],
    'active' => '1'
]);

// Felder für Tab 1: Empfänger
$formGenerator->addField([
    'type' => 'radio',
    'name' => 'document',
    'label' => 'Dokumentart wählen',
    'options' => $document_array,
    'required' => true,
    'value' => $document,
    'onchange' => "set_document_settings()",
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'faktura_company_id',
    'label' => 'Firma wählen',
    'options' => $company_array,
    'required' => true,
    'value' => $default_company_id,
    'onchange' => "set_document_settings()",
    'tab' => '1'
]);

$formGenerator->addField([
    'type' => 'grid',
    'columns' => 2,
    'tab' => '1',
    'fields' => [
        [
            'type' => 'input',
            'name' => 'bill_number',
            'label' => 'Folgenummer',
            'value' => $bill_number,
            'required' => true,
            'width' => 1
        ],
        [
            'type' => 'calendar',
            'name' => 'date_create',
            'label' => 'Erstelldatum',
            'value' => $date_create,
            'required' => true,
            'width' => 1
        ]
    ]
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'client_id',
    'label' => 'Kunden wählen',
    'options' => $client_array,
    'class' => 'search',
    'placeholder' => '--bitte wählen--',
    'clearable' => true,
    'tab' => '1'
]);

// Weitere Felder für Tab 1...

// Felder für Tab 2: Positionen
$formGenerator->addField([
    'type' => 'input',
    'name' => 'description',
    'label' => 'Betreff',
    'tab' => '2'
]);

$formGenerator->addField([
    'type' => 'dropdown',
    'name' => 'select_temp',
    'label' => 'Artikel',
    'options' => $article_array,
    'class' => 'search',
    'tab' => '2'
]);

// Weitere Felder für Tab 2...

// Felder für Tab 3: Zusatz
$formGenerator->addField([
    'type' => 'input',
    'name' => 'discount',
    'label' => 'Rabatt',
    'format' => 'euro',
    'tab' => '3'
]);

$formGenerator->addField([
    'type' => 'checkbox',
    'name' => 'no_mwst',
    'label' => 'Mwst. freie Rechnung',
    'tab' => '3'
]);

// Weitere Felder für Tab 3...

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

function getNextBillNumber($mysqli, $year, $document, $company_id)
{
    $query = "SELECT MAX(bill_number) as max_bill_number FROM bills 
              WHERE DATE_FORMAT(date_create,'%Y') = ? 
              AND document = ? AND company_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("isi", $year, $document, $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['max_bill_number'] + 1;
}