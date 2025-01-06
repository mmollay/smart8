<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../f_config.php';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'clients',
    'contentId' => 'content_clients',
    'itemsPerPage' => 30,
    'sortColumn' => $_GET['sort'] ?? 'client.client_number',
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

$year = $_SESSION['SetYear'] ?? 'all';
$yearCondition = ($year != 'all' && $year > 0) ? "AND DATE_FORMAT(date_membership_start,'%Y') <= $year 
    AND (DATE_FORMAT(date_membership_stop,'%Y-%m-%d') >= NOW() OR date_membership_stop = '0000-00-00')" : "";

$query = "
    SELECT 
        client.client_id, 
        CONCAT(client.firstname, ' ', client.secondname) as full_name,
        client.join_date,
        CASE
            WHEN (client.firstname != '' OR client.secondname != '') AND client.company_1 != '' 
                THEN CONCAT(client.company_1, ' (', client.firstname, ' ', client.secondname, ')')
            WHEN (client.firstname != '' OR client.secondname != '') AND client.company_1 = '' 
                THEN CONCAT(client.firstname, ' ', client.secondname)
            ELSE client.company_1
        END as company_1,
        IFNULL(DATE_FORMAT(client.reg_date, '%Y-%m-%d'), '') as reg_date,
        CONCAT('<i class=\"', client.country, ' flag\"></i>') as country,
        COUNT(DISTINCT tree.client_faktura_id) as tree_count,
        client.client_number,
        IF(client.abo, '<i class=\"green icon checkmark\"></i>', '<i class=\"icon disabled checkmark\"></i>') as abo,
        IF(client.newsletter, '<i class=\"green icon checkmark\"></i>', '<i class=\"icon disabled checkmark\"></i>') as newsletter,
        IF(client.post, '<i class=\"green icon checkmark\"></i>', '<i class=\"icon disabled checkmark\"></i>') as post,
        client.email,
        client.zip,
        client.city,
        client.birth,
        client.send_date,
        client.company_id,
        ROUND(IFNULL(SUM(CASE WHEN bills.date_storno = '0000-00-00' AND bills.document = 'rn' THEN bills.brutto ELSE 0 END), 0), 2) as brutto,
        ROUND(IFNULL(SUM(CASE WHEN bills.date_storno = '0000-00-00' AND bills.document = 'rn' THEN bills.booking_total ELSE 0 END), 0), 2) as booking_total,
        ROUND(IFNULL(SUM(CASE WHEN bills.date_storno = '0000-00-00' AND bills.document = 'rn' THEN bills.brutto - bills.booking_total ELSE 0 END), 0), 2) as amound_open,
        IF(client.tel != '', CONCAT('<button class=\"client_info\" title=\"Tel:', client.tel, '\">Info</button>'), '') as info
    FROM 
        client 
        LEFT JOIN bills ON client.client_id = bills.client_id 
        LEFT JOIN membership ON client.client_id = membership.client_id 
        LEFT JOIN sections ON client.client_id = sections.client_id 
        LEFT JOIN tree ON tree.client_faktura_id = client.client_id
        WHERE 1=1
    GROUP BY 
        client.client_id
";

$listGenerator->setSearchableColumns([
    'client.client_number',
    'client.company_1',
    'client.firstname',
    'client.secondname',
    'client.city',
    'client.zip',
    'client.email'
]);
$listGenerator->setDatabase($db, $query, true);

// Neue Filter hinzufügen
$listGenerator->addFilter('status', 'Status', [
    'client.abo = 1' => 'Abonnenten',
    'client.newsletter = 1' => 'Newsletter-Abonnenten',
    'client.post = 1' => 'Postversand',
    'amound_open > 0' => 'Offene Rechnungen',
    'tree_count > 0' => 'Mit Bäumen'
], ['filterType' => 'complex']);

$listGenerator->addFilter('country', 'Land', [
    'client.country = "de"' => 'Deutschland',
    'client.country = "at"' => 'Österreich',
    'client.country = "ch"' => 'Schweiz',
    'client.country NOT IN ("de", "at", "ch")' => 'Andere Länder'
], ['filterType' => 'complex']);

// $listGenerator->addFilter('join_date', 'Beitrittsdatum', [
//     'YEAR(client.join_date) = YEAR(CURDATE())' => 'Dieses Jahr',
//     'YEAR(client.join_date) = YEAR(CURDATE()) - 1' => 'Letztes Jahr',
//     'client.join_date <= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)' => 'Vor mehr als 2 Jahren'
// ], ['filterType' => 'complex']);

// Externe Buttons hinzufügen
$listGenerator->addExternalButton('new_client', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => '',
    'title' => 'Neuen Kunden anlegen',
    'modalId' => 'modal_form',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Kunden anzulegen'],
]);

// $listGenerator->addExternalButton('export_csv', [
//     'icon' => 'file excel outline',
//     'class' => 'ui green button',
//     'position' => 'inline',
//     'alignment' => 'right',
//     'title' => 'CSV Export',
//     'onclick' => 'exportToCSV()',
// ]);

// Definition der Tabellenspalten
$columns = [
    ['name' => 'client_id', 'label' => 'ID'],
    ['name' => 'client_number', 'label' => 'Kd.Nr'],
    ['name' => 'company_1', 'label' => 'Firma'],
    ['name' => 'zip', 'label' => 'PLZ'],
    ['name' => 'city', 'label' => 'Ort'],
    ['name' => 'country', 'label' => 'Land', 'allowHtml' => true],
    ['name' => 'tree_count', 'label' => 'Bäume', 'align' => 'center'],
    ['name' => 'email', 'label' => 'E-Mail'],
    ['name' => 'newsletter', 'label' => 'NL', 'align' => 'center', 'allowHtml' => true],
    [
        'name' => 'amound_open',
        'label' => 'Offen',
        'align' => 'right',
        'formatter' => 'euro',
        'showTotal' => true,

    ],
    [
        'name' => 'booking_total',
        'label' => 'Verbucht',
        'align' => 'right',
        'formatter' => 'euro',
        'showTotal' => true,

    ],
    [
        'name' => 'brutto',
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
    'modal_form' => ['title' => 'Kunden bearbeiten', 'content' => 'form/f_clients.php', 'size' => 'large'],
    'modal_form_delete' => ['title' => 'Kunden entfernen', 'content' => 'pages/form_delete.php', 'size' => 'small'],
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
        'params' => ['update_id' => 'client_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'client_id'],
        'conditions' => [
            function ($row) {
                return $row['brutto'] < 0.00 && $row['tree_count'] == 0;
            }
        ]
    ],
    'send_email' => [
        'icon' => 'mail',
        'position' => 'right',
        'class' => 'ui green mini button',
        'modalId' => 'modal_send_email',
        'popup' => 'E-Mail senden',
        'params' => ['client_id' => 'client_id'],
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

// JavaScript für CSV-Export
echo "
<script>
function exportToCSV() {
    // Implementierung des CSV-Exports
    alert('CSV-Export-Funktion wird aufgerufen');
}
</script>
";

// Schließen der Datenbankverbindung
if (isset($db)) {
    $db->close();
}
?>