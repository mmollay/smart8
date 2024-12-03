<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../e_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'items',
    'contentId' => 'content_items',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? 'id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Einträge gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
]);

// Datenbankabfrage
$query = "
    SELECT 
        id,
        title,
        description,
        created_at,
        status
    FROM 
        items
";

$listGenerator->setSearchableColumns(['title', 'description']);
$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$listGenerator->addColumn('id', 'ID', ['width' => '80px']);
$listGenerator->addColumn('title', 'Titel');
$listGenerator->addColumn('description', 'Beschreibung');
$listGenerator->addColumn('status', 'Status', [
    'replace' => [
        '1' => '<span class="ui green label">Aktiv</span>',
        '0' => '<span class="ui grey label">Inaktiv</span>'
    ],
    'allowHtml' => true
]);
$listGenerator->addColumn('created_at', 'Erstellt am', [
    'formatter' => 'datetime'
]);

// Buttons
$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neu',
    'modalId' => 'modal_form',
    'popup' => ['content' => 'Neuen Eintrag erstellen']
]);

// Modals
$listGenerator->addModal('modal_form', [
    'title' => 'Eintrag bearbeiten',
    'content' => 'form/f_items.php',
    'size' => 'small',
]);

$listGenerator->addModal('modal_delete', [
    'title' => 'Eintrag löschen',
    'content' => 'pages/form_delete.php',
    'size' => 'small',
]);

// Aktionsbuttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'right',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui red mini button',
        'modalId' => 'modal_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'id']
    ],
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

echo $listGenerator->generateList();