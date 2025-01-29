<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

// Konstanten definieren
const ITEMS_PER_PAGE = 25;

// ListGenerator Konfiguration
$config = [
    'listId' => 'users',
    'contentId' => 'content_users',
    'itemsPerPage' => ITEMS_PER_PAGE,
    'sortColumn' => $_GET['sort'] ?? 'username',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine Benutzer gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
    'allowHtml' => true,
    'tableClasses' => 'ui celled striped small compact very selectable table'
];

$listGenerator = new ListGenerator($config);

// SQL-Query mit Join für Parameter Model
$query = "
    SELECT 
       u.*,
       CONCAT(
           first_name, 
           ' ', 
           last_name
       ) AS full_name,
       CASE 
           WHEN COUNT(ac.id) > 0 THEN 
               CONCAT(
                   '<div class=\"ui mini labels api-keys-container\">',
                   GROUP_CONCAT(
                       DISTINCT CONCAT(
                           '<div class=\"ui ', 
                           CASE WHEN ac.is_active = 1 THEN 'teal' ELSE 'grey' END,
                           ' mini label\" data-tooltip=\"', ac.description, '\">',
                           '<i class=\"key icon\"></i>',
                           ac.platform,
                           '</div>'
                       )
                       ORDER BY ac.platform
                       SEPARATOR ''
                   ),
                   '</div>'
               )
           ELSE 
               '<div class=\"ui mini grey label\">Keine API-Keys</div>'
       END as api_keys,
       CASE 
           WHEN COUNT(ba.id) > 0 THEN 
               CONCAT(
                   '<div class=\"ui mini labels bank-accounts-container\">',
                   GROUP_CONCAT(
                       DISTINCT CONCAT(
                           '<div class=\"ui basic mini label\" data-tooltip=\"IBAN: ', 
                           ba.iban, 
                           '\">',
                           '<i class=\"university icon\"></i>',
                           ba.bank_name,
                           CASE WHEN ba.is_primary = 1 THEN ' <i class=\"star icon\"></i>' ELSE '' END,
                           '</div>'
                       )
                       ORDER BY ba.is_primary DESC, ba.bank_name
                       SEPARATOR ''
                   ),
                   '</div>'
               )
           ELSE 
               '<div class=\"ui mini grey label\">Keine Bankverbindungen</div>'
       END as bank_accounts,
       CASE 
           WHEN active = 1 THEN '<i class=\"green circle icon\" data-tooltip=\"Aktiv\"></i>'
           ELSE '<i class=\"red circle icon\" data-tooltip=\"Inaktiv\"></i>'
       END as status,
       COALESCE(SUM(CASE WHEN t.type = 'deposit' THEN t.amount ELSE 0 END), 0) as total_deposits,
       COALESCE(SUM(CASE WHEN t.type = 'withdrawal' THEN t.amount ELSE 0 END), 0) as total_withdrawals,
       tpm.name as parameter_model_name
   FROM users u
   LEFT JOIN api_credentials ac ON u.id = ac.user_id
   LEFT JOIN bank_accounts ba ON u.id = ba.user_id
   LEFT JOIN transactions t ON u.id = t.client_id
   LEFT JOIN trading_parameter_models tpm ON u.default_parameter_model_id = tpm.id
   GROUP BY u.id
";

$listGenerator->setSearchableColumns(['first_name', 'last_name', 'email', 'username', 'phone', 'company', 'parameter_model_name']);
$listGenerator->setDatabase($db, $query, true);

$columns = [
    ['name' => 'status', 'label' => '', 'width' => '30px', 'allowHtml' => true],
    ['name' => 'full_name', 'label' => "<i class='user icon'></i>Name"],
    ['name' => 'email', 'label' => "<i class='mail icon'></i>E-Mail"],
    ['name' => 'phone', 'label' => "<i class='phone icon'></i>Telefon"],
    [
        'name' => 'api_keys',
        'label' => "<i class='key icon'></i>API-Keys",
        'allowHtml' => true,
        'width' => '250px'
    ],
    [
        'name' => 'bank_accounts',
        'label' => "<i class='university icon'></i>Bankverbindungen",
        'allowHtml' => true,
        'width' => '250px'
    ],
    [
        'name' => 'parameter_model_name',
        'label' => "<i class='sliders horizontal icon'></i>Trading Model",
        'width' => '150px',
        'allowHtml' => true,
        'formatter' => function ($value) {
            return $value ?: '<i class="grey minus icon"></i>';
        }
    ],
    [
        'name' => 'total_deposits',
        'label' => "<i class='arrow down icon'></i>Einzahlungen",
        'width' => '120px',
        'formatter' => 'euro',
        'align' => 'right',
        'showTotal' => true,
        'totalType' => 'sum'
    ],
    [
        'name' => 'total_withdrawals',
        'label' => "<i class='arrow up icon'></i>Auszahlungen",
        'width' => '120px',
        'formatter' => 'euro',
        'align' => 'right',
        'showTotal' => true,
        'totalType' => 'sum'
    ]
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], [
        'allowHtml' => $column['allowHtml'] ?? false,
        'width' => $column['width'] ?? null,
        'formatter' => $column['formatter'] ?? null,
        'align' => $column['align'] ?? 'left',
        'showTotal' => $column['showTotal'] ?? false,
        'totalType' => $column['totalType'] ?? null
    ]);
}

$listGenerator->addModal('modal_form_user', [
    'title' => 'Benutzer bearbeiten',
    'content' => 'form/f_users.php',
    'size' => 'large'
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Benutzer entfernen',
    'content' => 'pages/form_delete.php',
    'size' => 'small'
]);

$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_user',
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
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

$listGenerator->addExternalButton('new_user', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'inline',
    'alignment' => '',
    'title' => 'Neuen Benutzer anlegen',
    'modalId' => 'modal_form_user',
    'popup' => ['content' => 'Neuen Benutzer anlegen']
]);

$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

echo $listGenerator->generateList();
?>

<style>
    .api-keys-container,
    .bank-accounts-container {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .ui.mini.label {
        margin: 0;
        padding: 4px 8px;
        font-size: 0.85em;
    }

    .ui.mini.basic.label {
        margin: 0;
        padding: 4px 8px;
        font-size: 0.85em;
    }

    .ui.mini.grey.label {
        background-color: #f5f5f5 !important;
        color: #767676 !important;
    }

    .ui.mini.label i.icon {
        margin-right: 4px;
        opacity: 0.8;
    }

    .star.icon {
        color: #fbbd08;
        margin-left: 4px !important;
    }

    .api-keys-container .ui.label:hover,
    .bank-accounts-container .ui.label:hover {
        background-color: #f8f9fa !important;
    }
</style>