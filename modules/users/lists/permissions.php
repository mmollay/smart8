<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../users_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'permissions',
    'contentId' => 'content_permissions',
    'itemsPerPage' => 20,
    'sortColumn' => 'user_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Berechtigungen gefunden.',
    'striped' => true,
    'celled' => true
]);

$query = "
   SELECT 
       ump.id,
       u.user_name,
       u.firstname,
       u.secondname,
       m.name as module_name,
       m.identifier as module_identifier,
       ump.permission_key,
       um.assigned_at 
   FROM 
       user_module_permissions ump
       JOIN user2company u ON u.user_id = ump.user_id
       JOIN modules m ON m.module_id = ump.module_id
       LEFT JOIN user_modules um ON um.user_id = u.user_id 
       AND um.module_id = m.module_id
   WHERE 1=1
";

$listGenerator->setSearchableColumns(['user_name', 'firstname', 'secondname', 'module_name', 'permission_key']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addColumn('user_name', 'Benutzer');
$listGenerator->addColumn('firstname', 'Vorname');
$listGenerator->addColumn('secondname', 'Nachname');
$listGenerator->addColumn('module_name', 'Modul');
$listGenerator->addColumn('module_identifier', 'Modul ID');
$listGenerator->addColumn('permission_key', 'Berechtigung');
$listGenerator->addColumn('assigned_at', 'Zugewiesen am', [
    'formatter' => 'datetime'
]);

$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neue Berechtigung',
    'modalId' => 'modal_permission_form',
    'popup' => ['content' => 'Neue Berechtigung zuweisen']
]);

$listGenerator->addModal('modal_permission_form', [
    'title' => 'Berechtigung zuweisen',
    'content' => 'form/f_permission.php',
    'size' => 'small'
]);

$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'right',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_permission_form',
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
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

echo $listGenerator->generateList();