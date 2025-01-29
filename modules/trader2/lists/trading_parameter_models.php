<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

// Konstanten definieren
const ITEMS_PER_PAGE = 25;

// ListGenerator Konfiguration
$config = [
    'listId' => 'parameter_models',
    'contentId' => 'content_parameter_models',
    'itemsPerPage' => ITEMS_PER_PAGE,
    'sortColumn' => $_GET['sort'] ?? 'name',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'ASC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine Parameter Modelle gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
    'allowHtml' => true,
    'tableClasses' => 'ui celled striped small compact very selectable table'
];

$listGenerator = new ListGenerator($config);

// SQL-Query mit Join für Parameter Werte
$query = "
    SELECT 
        pm.*,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'tp_percentage_long' LIMIT 1) as tp_long,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'sl_percentage_long' LIMIT 1) as sl_long,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'leverage' LIMIT 1) as leverage,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'position_size' LIMIT 1) as position_size,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'tp_percentage_short' LIMIT 1) as tp_short,
        (SELECT parameter_value FROM trading_parameter_model_values 
         WHERE model_id = pm.id AND parameter_name = 'sl_percentage_short' LIMIT 1) as sl_short
    FROM trading_parameter_models pm
";

$listGenerator->setDatabase($db, $query, true);

// Spaltenkonfiguration
$listGenerator->addColumn(
    'name',
    "<i class='tag icon'></i>Name",
    ['width' => '180px']
);
$listGenerator->addColumn(
    'is_active',
    '',
    [
        'width' => '30px',
        'allowHtml' => true,
        'formatter' => function ($value) {
            return $value ? "<i class='check circle green icon'></i>" : "<i class='times circle red icon'></i>";
        }
    ]
);
$listGenerator->addColumn(
    'description',
    "<i class='info circle icon'></i>Beschreibung"
);
$listGenerator->addColumn(
    'position_size',
    "<i class='ethereum icon'></i>Size",
    [
        'width' => '80px',
        'formatter' => function ($value) {
            return $value ? number_format((float)$value, 3) . ' ETH' : '-';
        }
    ]
);
$listGenerator->addColumn(
    'leverage',
    "<i class='chart line icon'></i>Hebel",
    [
        'width' => '60px',
        'formatter' => function ($value) {
            return $value ? $value . 'x' : '-';
        }
    ]
);
$listGenerator->addColumn(
    'tp_long',
    "<i class='arrow up green icon'></i>TP Long",
    [
        'width' => '80px',
        'formatter' => function ($value) {
            return $value ? number_format((float)$value, 1) . '%' : '-';
        }
    ]
);
$listGenerator->addColumn(
    'sl_long',
    "<i class='arrow down red icon'></i>SL Long",
    [
        'width' => '80px',
        'formatter' => function ($value) {
            return $value ? number_format((float)$value, 1) . '%' : '-';
        }
    ]
);
$listGenerator->addColumn(
    'tp_short',
    "<i class='arrow down green icon'></i>TP Short",
    [
        'width' => '80px',
        'formatter' => function ($value) {
            return $value ? number_format((float)$value, 1) . '%' : '-';
        }
    ]
);
$listGenerator->addColumn(
    'sl_short',
    "<i class='arrow up red icon'></i>SL Short",
    [
        'width' => '80px',
        'formatter' => function ($value) {
            return $value ? number_format((float)$value, 1) . '%' : '-';
        }
    ]
);

// Suchbare Spalten
$listGenerator->setSearchableColumns([
    'name',
    'description'
]);

// Modal für Parameter-Modell Formular
$listGenerator->addModal('modal_form_parameter_model', [
    'title' => 'Parameter Modell bearbeiten',
    'content' => 'form/f_trading_parameters.php',
    'size' => 'large'
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Benutzer entfernen',
    'content' => 'pages/form_delete.php',
    'size' => 'small'
]);

// Button-Konfiguration
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_parameter_model',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'id']
    ],
    'clone' => [
        'icon' => 'clone',
        'position' => 'left',
        'class' => 'ui teal mini button',
        'modalId' => 'modal_form_parameter_model',
        'popup' => 'Duplizieren',
        'params' => ['clone_id' => 'id']
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

// Buttons zum ListGenerator hinzufügen
foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Externes Button für neues Modell
$listGenerator->addExternalButton('new_model', [
    'icon' => 'plus',
    'class' => 'ui blue circular button',
    'position' => 'inline',
    'alignment' => '',
    'title' => 'Neues Modell anlegen',
    'modalId' => 'modal_form_parameter_model',
    'popup' => ['content' => 'Neues Parameter Modell anlegen']
]);

// Liste generieren
echo $listGenerator->generateList();
?>

<script>
    /**
     * Toast-Nachricht anzeigen
     * @param {string} message - Anzuzeigende Nachricht
     * @param {string} type - Art des Toasts ('success', 'error', 'warning', 'info')
     */
    function showToast(message, type = 'info') {
        $('body').toast({
            message: message,
            class: type,
            position: 'top right',
            showProgress: 'bottom'
        });
    }

    /**
     * Modell löschen
     * @param {number} id - ID des zu löschenden Modells
     */
    function deleteModel(id) {
        confirmDelete({
            url: 'ajax/delete_record.php',
            data: {
                id: id,
                table: 'trading_parameter_models'
            },
            success: function () {
                reloadList('content_parameter_models');
                showToast('Modell wurde erfolgreich gelöscht', 'success');
            }
        });
    }
</script>