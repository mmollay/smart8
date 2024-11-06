<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'customers',
    'contentId' => 'content_customers',
    'itemsPerPage' => 30,
    'sortColumn' => $_GET['sort'] ?? 'customers.customer_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Kunden gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped definition small compact selectable table',
    'debug' => true,
];

$listGenerator = new ListGenerator($listConfig);

$query = "
    SELECT 
        customers.customer_id, 
        customers.company_name,
        customers.contact_person,
        customers.email,
        customers.postal_code,
        customers.city,
        customers.country,
        COUNT(DISTINCT invoices.invoice_id) as invoice_count,
        IFNULL(SUM(invoices.total_amount), 0) as total_invoiced,
        IFNULL(SUM(CASE WHEN invoices.paid = 0 THEN invoices.total_amount ELSE 0 END), 0) as total_unpaid
    FROM 
        customers 
        LEFT JOIN invoices ON customers.customer_id = invoices.customer_id
    GROUP BY 
        customers.customer_id
";

$listGenerator->setSearchableColumns([
    'customers.company_name',
    'customers.contact_person',
    'customers.city',
    'customers.postal_code',
    'customers.email'
]);
$listGenerator->setDatabase($db, $query, true);

// Neue Filter hinzufügen
$listGenerator->addFilter('country', 'Land', [
    'customers.country = "DE"' => 'Deutschland',
    'customers.country = "AT"' => 'Österreich',
    'customers.country = "CH"' => 'Schweiz',
    'customers.country NOT IN ("DE", "AT", "CH")' => 'Andere Länder'
], ['filterType' => 'complex']);

$listGenerator->addFilter('invoice_status', 'Rechnungsstatus', [
    'total_unpaid > 0' => 'Offene Rechnungen',
    'invoice_count > 0' => 'Mit Rechnungen'
], ['filterType' => 'complex']);

// Externe Buttons hinzufügen
$listGenerator->addExternalButton('new_customer', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => '',
    'title' => 'Neuen Kunden anlegen',
    'modalId' => 'modal_form',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Kunden anzulegen'],
]);

// Definition der Tabellenspalten
$columns = [
    ['name' => 'customer_id', 'label' => 'ID'],
    ['name' => 'company_name', 'label' => 'Firma'],
    ['name' => 'contact_person', 'label' => 'Ansprechpartner'],
    ['name' => 'postal_code', 'label' => 'PLZ'],
    ['name' => 'city', 'label' => 'Ort'],
    ['name' => 'country', 'label' => 'Land'],
    ['name' => 'invoice_count', 'label' => 'Rechnungen', 'align' => 'center'],
    ['name' => 'email', 'label' => 'E-Mail'],
    [
        'name' => 'total_unpaid',
        'label' => 'Offen',
        'align' => 'right',
        'formatter' => 'euro',
        'showTotal' => true,
    ],
    [
        'name' => 'total_invoiced',
        'label' => 'Gesamt',
        'align' => 'right',
        'formatter' => 'euro',
        'showTotal' => true,
    ],
];

// Hinzufügen der Spalten zum ListGenerator
foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Definition der Modals
$modals = [
    'modal_form' => ['title' => 'Kunden bearbeiten', 'content' => 'form/f_customers.php', 'size' => 'large'],
    'modal_form_delete' => ['title' => 'Kunden entfernen', 'content' => 'form/f_delete.php', 'size' => 'small'],
    'modal_send_email' => ['title' => 'E-Mail senden', 'content' => 'form/f_send_email.php', 'size' => 'medium'],
];

// Hinzufügen der Modals zum ListGenerator
foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Definition der Aktions-Buttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'customer_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => [
            'delete_id' => 'customer_id',
            'entity_type' => ['type' => 'fixed', 'value' => 'customer']
        ],
        'conditions' => [
            function ($row) {
                return $row['total_invoiced'] == 0;
            }
        ]
    ],
    'send_email' => [
        'icon' => 'mail',
        'position' => 'right',
        'class' => 'ui green mini button',
        'modalId' => 'modal_send_email',
        'popup' => 'E-Mail senden',
        'params' => ['customer_id'],
        'conditions' => [
            function ($row) {
                return !empty($row['email']);
            }
        ]
    ],
];

// Hinzufügen der Buttons zum ListGenerator
foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'left');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Generieren und Ausgeben der Liste
echo $listGenerator->generateList();

// Schließen der Datenbankverbindung
if (isset($db)) {
    $db->close();
}
?>