<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../users_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'users',
    'contentId' => 'content_users',
    'itemsPerPage' => 20,
    'sortColumn' => 'user_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Einträge gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
]);

// Modified query to handle encoding properly
$query = "
    SELECT 
        u.user_id,
        CASE 
            WHEN u.superuser = 1 THEN CONCAT('<span class=\"ui blue tiny label\">Admin</span> ',u.user_name)
            ELSE u.user_name
        END as user_name,
        u.firstname,
        u.secondname,
        u.city,
        u.verified,
        GROUP_CONCAT(DISTINCT m.name) as assigned_modules
    FROM 
        user2company u
        LEFT JOIN user_modules um ON u.user_id = um.user_id AND um.status = 1
        LEFT JOIN modules m ON um.module_id = m.module_id
    GROUP BY u.user_id
";

$listGenerator->setSearchableColumns(['user_name', 'firstname', 'secondname', 'city']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addColumn('user_id', 'ID');
$listGenerator->addColumn('user_name', 'E-Mail', ['allowHtml' => true]);
$listGenerator->addColumn('firstname', 'Vorname');
$listGenerator->addColumn('secondname', 'Nachname');
$listGenerator->addColumn('city', 'Stadt');
$listGenerator->addColumn('assigned_modules', 'Module');
$listGenerator->addColumn('verified', 'Status', [
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
    'title' => 'Neuer Benutzer',
    'modalId' => 'modal_user_form',
    'popup' => ['content' => 'Neuen Benutzer erstellen']
]);

$listGenerator->addModal('modal_user_form', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'form/f_user.php',
    'size' => 'small',
]);

$listGenerator->addModal('modal_delete', [
    'title' => 'Benutzer löschen',
    'content' => 'form/f_delete.php',
    'size' => 'small',
]);

$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'right',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_user_form',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'user_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui red mini button',
        'modalId' => 'modal_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'user_id']
    ],
    'modules' => [
        'icon' => 'cubes',
        'position' => 'right',
        'class' => 'ui teal mini button',
        'modalId' => 'modal_module_assign',
        'popup' => 'Module zuweisen',
        'params' => ['user_id' => 'user_id']
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

echo $listGenerator->generateList();