<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$str_button_name = 'Konten';
$str_button_name2 = 'Konto';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'account_list',
    'contentId' => 'content_accounts',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'account_name',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine $str_button_name gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped definition small compact very selectable table',
    'debug' => true,
];

$listGenerator = new ListGenerator($listConfig);

// Hauptabfrage
$query = "
    SELECT 
        account_id, 
        account_number,
        account_name,
        account_type,
        IFNULL(percentage, '') as percentage
    FROM 
        accounts
";

$listGenerator->setSearchableColumns([
    'account_number',
    'account_name',
    'account_type'
]);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'account_number', 'label' => 'Kontonummer'],
    ['name' => 'account_name', 'label' => 'Kontoname'],
    ['name' => 'account_type', 'label' => 'Kontotyp'],
    ['name' => 'percentage', 'label' => 'Prozentsatz (%)', 'align' => 'right']
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Buttons definieren
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'blue mini',
        'modalId' => 'modal_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'account_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'account_id', 'entity_type' => ['type' => 'fixed', 'value' => 'account']],
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_form' => ['title' => "<i class='icon edit'></i> $str_button_name2 bearbeiten", 'content' => 'form/f_accounts.php', 'size' => 'large'],
    'modal_form_delete' => ['title' => "$str_button_name2 entfernen", 'class' => 'small', 'content' => 'form/f_delete.php'],
];

foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Externe Buttons
$listGenerator->addExternalButton('new_entry', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'top',
    'alignment' => 'left',
    'title' => "$str_button_name2 erstellen",
    'modalId' => 'modal_form',
]);

// Filter hinzufügen
$listGenerator->addFilter('account_type_filter', 'Kontotyp', [
    'account_type = "Income"' => 'Einnahmen',
    'account_type = "Expense"' => 'Ausgaben',
    'account_type = "Bank"' => 'Bank'
], [
    'filterType' => 'complex',
    'placeholder' => 'Kontotyp auswählen'
]);

$listGenerator->addFilter('percentage_filter', 'Prozentsatz', [
    'percentage IS NOT NULL' => 'Mit Prozentsatz',
    'percentage IS NULL' => 'Ohne Prozentsatz',
    'percentage > 0' => 'Prozentsatz > 0'
], [
    'filterType' => 'complex',
    'placeholder' => 'Prozentsatz filtern'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>