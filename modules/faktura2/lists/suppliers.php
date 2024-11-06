<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

$str_button_name = 'Lieferanten';
$str_button_name2 = 'Lieferant';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'supplier_list',
    'contentId' => 'content_suppliers',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'company_name',
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
        supplier_id, 
        company_name,
        contact_person,
        email,
        phone,
        city,
        country
    FROM 
        suppliers
";

$listGenerator->setSearchableColumns([
    'company_name',
    'contact_person',
    'email',
    'city',
    'country'
]);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'company_name', 'label' => 'Firmenname'],
    ['name' => 'contact_person', 'label' => 'Ansprechpartner'],
    ['name' => 'email', 'label' => 'E-Mail'],
    ['name' => 'phone', 'label' => 'Telefon'],
    ['name' => 'city', 'label' => 'Stadt'],
    ['name' => 'country', 'label' => 'Land']
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
        'modalId' => 'modal_form_edit',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'supplier_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'mini',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'supplier_id', 'entity_type' => ['type' => 'fixed', 'value' => 'supplier']],
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals definieren
$modals = [
    'modal_form_edit' => ['title' => "<i class='icon edit'></i> $str_button_name2 bearbeiten", 'content' => 'form/f_suppliers.php'],
    'modal_form' => ['title' => "$str_button_name2 bearbeiten", 'content' => 'form/f_suppliers.php', 'size' => 'large'],
    'modal_form_new' => ['title' => "<i class='icon edit'></i> $str_button_name2 erstellen", 'content' => "form/f_suppliers.php"],
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
$listGenerator->addFilter('country_filter', 'Land', [
    'country = "Deutschland"' => 'Deutschland',
    'country = "Österreich"' => 'Österreich',
    'country = "Schweiz"' => 'Schweiz',
    'country NOT IN ("Deutschland", "Österreich", "Schweiz")' => 'Andere Länder'
], [
    'filterType' => 'complex',
    'placeholder' => 'Land auswählen'
]);

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>