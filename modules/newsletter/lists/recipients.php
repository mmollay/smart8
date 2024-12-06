<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
require __DIR__ . '/../n_config.php';

// ListGenerator Konfiguration
$listConfig = [
    'listId' => 'recipients',
    'contentId' => 'content_recipients',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'r.id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Empfänger gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
];

$listGenerator = new ListGenerator($listConfig);

// Basis-Query
$baseQuery = "
    SELECT
        r.id,
        r.first_name,
        r.last_name,
        r.company,
        r.email,
        r.gender,
        r.title,
        r.comment,
        r.unsubscribed,
        r.unsubscribed_at,
        r.bounce_status,
        r.last_bounce_at,
        GROUP_CONCAT(DISTINCT g.id) as group_ids,
        IFNULL(GROUP_CONCAT(
            DISTINCT CONCAT('<div class=\"ui mini basic compact label ', g.color, '\">', g.name, '</div>')
            SEPARATOR ' '
        ), '<div class=\"ui mini compact label\">Keine Gruppen</div>') as group_labels,
        CASE 
            WHEN r.unsubscribed = 1 THEN 
                CONCAT('<div class=\"ui red mini label\" title=\"Abgemeldet am ', 
                      DATE_FORMAT(r.unsubscribed_at, '%d.%m.%Y %H:%i'), 
                      '\"><i class=\"user times icon\"></i>Abgemeldet</div>')
            WHEN r.bounce_status = 'hard' THEN 
                CONCAT('<div class=\"ui orange mini label\" title=\"Hard Bounce am ',
                      DATE_FORMAT(r.last_bounce_at, '%d.%m.%Y %H:%i'),
                      '\"><i class=\"exclamation triangle icon\"></i>Hard Bounce</div>')
            WHEN r.bounce_status = 'soft' THEN 
                CONCAT('<div class=\"ui yellow mini label\" title=\"Soft Bounce am ',
                      DATE_FORMAT(r.last_bounce_at, '%d.%m.%Y %H:%i'),
                      '\"><i class=\"exclamation circle icon\"></i>Soft Bounce</div>')
            ELSE '<div class=\"ui green mini label\"><i class=\"user check icon\"></i>Aktiv</div>'
        END as status_label
    FROM recipients r
    LEFT JOIN recipient_group rg ON r.id = rg.recipient_id
    LEFT JOIN groups g ON rg.group_id = g.id
    WHERE r.user_id = '$userId'
";

// Bedingungen basierend auf Filtern
$where = [];
$params = [];
$types = "";

// Status Filter
if (isset($_GET['status']) && $_GET['status'] !== '') {
    switch ($_GET['status']) {
        case 'active':
            $where[] = "r.unsubscribed = 0 AND r.bounce_status = 'none'";
            break;
        case 'unsubscribed':
            $where[] = "r.unsubscribed = 1";
            break;
        case 'bounced_hard':
            $where[] = "r.bounce_status = 'hard'";
            break;
        case 'bounced_soft':
            $where[] = "r.bounce_status = 'soft'";
            break;
    }
}

// Gruppen Filter
if (isset($_GET['group_id']) && $_GET['group_id'] !== '') {
    $where[] = "g.id = ?";
    $params[] = $_GET['group_id'];
    $types .= "i";
}

// WHERE-Klausel zusammenbauen
$whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Vollständige Query
$query = $baseQuery . $whereClause . " GROUP BY r.id";

$listGenerator->setSearchableColumns(['email', 'first_name', 'last_name', 'company', 'comment']);
$listGenerator->setDatabase($db, $query, true, $types, $params);

// Filter
$listGenerator->addFilter('group_id', 'Gruppe', getAllGroups($db));

$listGenerator->addFilter('status', 'Status', [
    'active' => '<i class="check circle green icon"></i>Aktiv',
    'unsubscribed' => '<i class="times circle red icon"></i>Abgemeldet',
    'bounced_hard' => '<i class="exclamation triangle orange icon"></i>Hard Bounce',
    'bounced_soft' => '<i class="exclamation circle yellow icon"></i>Soft Bounce'
]);

// Externe Buttons
$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuen Empfänger anlegen',
    'modalId' => 'modal_form2',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Empfänger hinzuzufügen']
]);


$listGenerator->addExternalButton('export', [
    'icon' => 'download',
    'class' => 'ui green button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'CSV Export',
    'onclick' => 'window.location.href="ajax/export.php?type=recipients&format=csv"'
]);

// $listGenerator->addExport([
//     'url' => 'ajax/export_recipients.php',
//     'format' => 'xlsx',
//     'fields' => ['id', 'name', 'email'],
//     'title' => 'Excel Export',
//     'popup' => ['content' => 'Als Excel exportieren'],
//     'beforeExport' => 'function(params) {
//         console.log("Export startet", params);
//         return confirm("Export starten?");
//     }',
//     'afterExport' => 'function(params) {
//         console.log("Export abgeschlossen", params);
//     }'
// ]);

// $listGenerator->addExport([
//     'url' => 'ajax/generic_export.php',
//     'format' => 'csv',
//     //'fields' => ['id', 'first_name', 'last_name'],
//     'title' => 'CSV Export',
//     'popup' => ['content' => 'Liste exportieren'],
//     'beforeExport' => 'function(params) {
//         return confirm("Möchten Sie die Liste exportieren?");
//     }'
// ]);



// Spalten definieren
$columns = [
    ['name' => 'first_name', 'label' => "<i class='user icon'></i>Vorname"],
    ['name' => 'last_name', 'label' => "<i class='user icon'></i>Nachname"],
    ['name' => 'company', 'label' => "<i class='building icon'></i>Firma"],
    ['name' => 'email', 'label' => "<i class='mail icon'></i>Empfänger-Email"],
    ['name' => 'status_label', 'label' => "<i class='check circle icon'></i>Status"],
    ['name' => 'group_labels', 'label' => "Gruppennamen"],
    ['name' => 'comment', 'label' => "Kommentar"],
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], ['allowHtml' => true]);
}

// Modals definieren
$modals = [
    'modal_form2' => [
        'title' => 'Empfänger bearbeiten',
        'content' => 'form/f_recipients.php',
        'size' => 'small',
    ],
    'modal_form_delete' => [
        'title' => 'Empfänger entfernen',
        'content' => 'pages/form_delete.php',
        'size' => 'small',
    ],
];

foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Buttons definieren
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form2',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'id']
    ],
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}



// Setzen der Spaltentitel und Ausrichtung für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Generiere und gib die Liste aus
echo $listGenerator->generateList();

// Schließe die Datenbankverbindung
if (isset($db)) {
    $db->close();
}