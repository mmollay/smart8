<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../users_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'modules',
    'contentId' => 'content_modules',
    'itemsPerPage' => 20,
    'sortColumn' => 'module_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Module gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
]);

$query = "
   SELECT 
       m.*,
       COUNT(DISTINCT um.user_id) as user_count
   FROM 
       modules m
       LEFT JOIN user_modules um ON m.module_id = um.module_id AND um.status = 1
   GROUP BY m.module_id
";

$listGenerator->setSearchableColumns(['name', 'identifier', 'description']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addColumn('module_id', 'ID', ['width' => '80px']);
$listGenerator->addColumn('name', 'Name');
$listGenerator->addColumn('identifier', 'Identifier');
$listGenerator->addColumn('description', 'Beschreibung');
$listGenerator->addColumn('user_count', 'Aktive Benutzer');
$listGenerator->addColumn('status', 'Status', [
    'replace' => [
        '1' => '<span class="ui green label">Aktiv</span>',
        '0' => '<span class="ui grey label">Inaktiv</span>'
    ],
    'allowHtml' => true
]);

$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neues Modul',
    'modalId' => 'modal_module_form',
    'popup' => ['content' => 'Neues Modul erstellen']
]);

$listGenerator->addModal('modal_module_form', [
    'title' => 'Modul bearbeiten',
    'content' => 'form/f_module.php',
    'size' => 'small',
]);

$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'right',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_module_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'module_id']
    ],
    'users' => [
        'icon' => 'users',
        'position' => 'right',
        'class' => 'ui teal mini button',
        'modalId' => 'modal_module_users',
        'popup' => 'Benutzer verwalten',
        'params' => ['module_id' => 'module_id']
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

echo $listGenerator->generateList();