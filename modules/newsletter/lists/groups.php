<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'groups',
    'contentId' => 'content_group',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? null,
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
    'debug' => true,
]);

// Datenbank-Abfrage
$query = "
    SELECT 
        g.id as group_id, 
        CONCAT('<div class=\"ui ', g.color, ' compact empty mini circular label\"></div> ', g.name) as group_name, 
        COUNT(DISTINCT r.id) as recipients_count, 
        g.created_at
    FROM 
        groups g
        LEFT JOIN recipient_group rg ON g.id = rg.group_id
        LEFT JOIN recipients r ON rg.recipient_id = r.id
    GROUP BY 
        g.id
";

$listGenerator->setSearchableColumns(['g.name']); // Ändern Sie 'group_name' zu 'g.name'
$listGenerator->setDatabase($db, $query, true);

// Externe Buttons
$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuer Eintrag',
    'modalId' => 'modal_form_g',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Eintrag hinzuzufügen']
]);

// Spalten definieren
$listGenerator->addColumn('group_id', 'ID');
$listGenerator->addColumn('group_name', '<i class="users icon"></i>Gruppe', ['allowHtml' => true]);
$listGenerator->addColumn('recipients_count', 'Anzahl der Empfänger');
$listGenerator->addColumn('created_at', 'Erstellt am', [
    'formatter' => function ($value) {
        return date('d.m.Y H:i', strtotime($value));
    }
]);

// Modals definieren
$listGenerator->addModal('modal_form_g', [
    'title' => 'Gruppe bearbeiten',
    'content' => 'form/f_groups.php',
    'size' => 'small',
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Gruppe entfernen',
    'content' => 'pages/form_delete.php',
    'size' => 'small',
]);

$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_g',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'group_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'group_id']
    ],
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Setzen der Spaltentitel für die Buttons
// Setzen der Spaltentitel und Ausrichtung für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');  // Zentriert die Buttons in der linken Spalte
$listGenerator->setButtonColumnTitle('right', '', 'right');  // Richtet die Buttons in der rechten Spalte rechts aus

// Generiere und gib die Liste aus
echo $listGenerator->generateList();

// Schließe die Datenbankverbindung
if (isset($db)) {
    $db->close();
}