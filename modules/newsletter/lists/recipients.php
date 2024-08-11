<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

// Funktion zum Abrufen aller Gruppen
function getAllGroups($db)
{
    $groups = [];
    $query = "SELECT id, name FROM groups ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $groups[$row['id']] = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
    }
    return $groups;
}

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
    'tableClasses' => 'ui celled striped definition small compact table',
    //'debug' => true,
    'rememberFilters' => true,
];

$listGenerator = new ListGenerator($listConfig);

// Optimierte Datenbank-Abfrage
$query = "
    SELECT
        r.id,
        r.first_name,
        r.last_name,
        r.company,
        r.email,
        r.gender,
        r.title,
        r.comment,
        GROUP_CONCAT(DISTINCT g.id) as group_ids,
        IFNULL(GROUP_CONCAT(
            DISTINCT CONCAT('<div class=\"ui mini basic compact label ', g.color, '\">', g.name, '</div>')
            SEPARATOR ' '
        ), '<div class=\"ui mini compact label\">Keine Gruppen</div>') as group_labels
    FROM
        recipients r
    LEFT JOIN recipient_group rg ON r.id = rg.recipient_group_id
    LEFT JOIN groups g ON rg.group_id = g.id
    GROUP BY
        r.id
";

$listGenerator->setSearchableColumns(['email', 'first_name', 'last_name', 'company', 'comment']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addFilter('group_id', 'Gruppe', getAllGroups($db));

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

// Spalten definieren
$columns = [
    ['name' => 'first_name', 'label' => "<i class='user icon'></i>Vorname"],
    ['name' => 'last_name', 'label' => "<i class='user icon'></i>Nachname"],
    ['name' => 'company', 'label' => "<i class='building icon'></i>Firma"],
    ['name' => 'email', 'label' => "<i class='mail icon'></i>Empfänger-Email"],
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