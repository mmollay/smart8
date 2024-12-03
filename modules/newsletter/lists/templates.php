<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'templates',
    'contentId' => 'content_templates',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? 'created_at',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Templates gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
    'debug' => true,
]);

// Datenbank-Abfrage für Templates
$query = "
    SELECT 
        t.id as template_id,
        t.name as template_name,
        t.description,
        t.subject,
        LEFT(t.html_content, 150) as preview_content,
        t.created_at,
        t.updated_at,
        COALESCE(
            (SELECT COUNT(*) 
            FROM email_contents 
            WHERE template_id = t.id)
        , 0) as usage_count
    FROM 
        email_templates t
        WHERE 
        t.user_id = '$userId'
";

// Wenn eine Suche aktiv ist, wird diese in WHERE-Bedingungen umgewandelt
$searchColumns = ['t.name', 't.description', 't.subject'];
$listGenerator->setSearchableColumns($searchColumns);
$listGenerator->setDatabase($db, $query, true);

// Optionale Filter hinzufügen
$listGenerator->addFilter('usage', 'Verwendung', [
    'used' => 'Verwendet',
    'unused' => 'Ungenutzt'
]);

// Externe Buttons
$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neues Template',
    'modalId' => 'modal_form_template',
    'popup' => ['content' => 'Klicken Sie hier, um ein neues Template anzulegen']
]);

// Spalten definieren
$columns = [
    ['name' => 'template_id', 'label' => 'ID'],
    ['name' => 'template_name', 'label' => '<i class="file alternate outline icon"></i>Name', 'allowHtml' => true],
    ['name' => 'subject', 'label' => '<i class="envelope outline icon"></i>Betreff'],
    [
        'name' => 'preview_content',
        'label' => 'Vorschau',
        'formatter' => function ($value) {
            return '<div class="ui small text">' . htmlspecialchars(strip_tags($value)) . '...</div>';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'usage_count',
        'label' => '<i class="chart bar outline icon"></i>Verwendet',
        'formatter' => function ($value) {
            $color = $value > 0 ? 'teal' : 'grey';
            return '<div class="ui ' . $color . ' label">' . $value . '</div>';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'created_at',
        'label' => '<i class="calendar outline icon"></i>Erstellt',
        'formatter' => function ($value) {
            return date('d.m.Y H:i', strtotime($value));
        }
    ]
];

foreach ($columns as $column) {
    $listGenerator->addColumn(
        $column['name'],
        $column['label'],
        array_diff_key($column, array_flip(['name', 'label']))
    );
}

// Modals definieren
$listGenerator->addModal('modal_form_template', [
    'title' => 'Template bearbeiten',
    'content' => 'form/f_templates.php',
    'size' => 'large',
]);

$listGenerator->addModal('modal_preview_template', [
    'title' => 'Template Vorschau',
    'content' => 'form/preview_template.php',
    'size' => 'large',
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Template löschen',
    'content' => 'pages/form_delete.php',
    'size' => 'small',
]);

// Buttons definieren
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_template',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'template_id']
    ],
    'preview' => [
        'icon' => 'eye',
        'position' => 'left',
        'class' => 'ui teal mini button',
        'modalId' => 'modal_preview_template',
        'popup' => 'Vorschau',
        'params' => ['template_id' => 'template_id']
    ],
    'duplicate' => [
        'icon' => 'copy',
        'position' => 'left',
        'class' => 'ui olive mini button',
        'popup' => 'Duplizieren',
        'callback' => 'duplicateTemplate',
        'params' => ['template_id' => 'template_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui red mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'template_id']
    ]
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Button-Spalten-Titel setzen
$listGenerator->setButtonColumnTitle('left', '', 'left');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Liste generieren
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    function duplicateTemplate(params) {
        $.ajax({
            url: 'ajax/template/duplicate.php',
            method: 'POST',
            data: { template_id: params.template_id },
            success: function (response) {
                if (response.success) {
                    showToast('Template wurde dupliziert', 'success');
                    reloadTable();
                } else {
                    showToast('Fehler beim Duplizieren: ' + response.message, 'error');
                }
            },
            error: function () {
                showToast('Fehler beim Duplizieren des Templates', 'error');
            }
        });
    }
</script>