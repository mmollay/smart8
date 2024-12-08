<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'blacklist',
    'contentId' => 'content_blacklist',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? 'created_at',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Blacklist-Einträge gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
]);

// Erweiterte Query mit Join auf recipients für zusätzliche Informationen
$query = "
    SELECT 
        b.id,
        b.email,
        b.reason,
        b.source,
        b.created_at,
        CASE 
            WHEN b.source = 'manual' THEN '<div class=\"ui red mini label\">Manuell</div>'
            WHEN b.source = 'bounce' THEN '<div class=\"ui orange mini label\">Bounce</div>'
            WHEN b.source = 'spam' THEN '<div class=\"ui yellow mini label\">Spam</div>'
            WHEN b.source = 'complaint' THEN '<div class=\"ui brown mini label\">Beschwerde</div>'
        END as source_label,
        r.first_name,
        r.last_name,
        r.company
    FROM 
        blacklist b
        LEFT JOIN recipients r ON b.email = r.email AND b.user_id = r.user_id
    WHERE 
        b.user_id = '$userId'
        GROUP BY b.id
";

$listGenerator->setSearchableColumns(['b.email', 'b.reason', 'r.first_name', 'r.last_name', 'r.company']);
$listGenerator->setDatabase($db, $query, true);

// Filter für die Quelle
$listGenerator->addFilter('source', 'Quelle', [
    'manual' => 'Manuell',
    'bounce' => 'Bounce',
    'spam' => 'Spam',
    'complaint' => 'Beschwerde'
]);

// Button zum Hinzufügen neuer Einträge
$listGenerator->addExternalButton('add', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuer Eintrag',
    'modalId' => 'modal_form_blacklist',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Blacklist-Eintrag hinzuzufügen']
]);

// Spalten definieren
$columns = [
    ['name' => 'email', 'label' => '<i class="mail ban icon"></i>E-Mail', 'width' => '250px'],
    [
        'name' => 'recipient_info',
        'label' => '<i class="user icon"></i>Empfänger-Info',
        'formatter' => function ($value, $row) {
            if (empty($row['first_name']) && empty($row['last_name']) && empty($row['company'])) {
                return '<span class="ui grey text">Keine Informationen</span>';
            }

            $info = [];
            if (!empty($row['first_name']) || !empty($row['last_name'])) {
                $info[] = trim($row['first_name'] . ' ' . $row['last_name']);
            }
            if (!empty($row['company'])) {
                $info[] = $row['company'];
            }
            return implode('<br>', $info);
        },
        'allowHtml' => true,
        'width' => '200px'
    ],
    ['name' => 'source_label', 'label' => '<i class="filter icon"></i>Quelle', 'allowHtml' => true, 'width' => '100px'],
    ['name' => 'reason', 'label' => '<i class="comment icon"></i>Grund'],
    [
        'name' => 'created_at',
        'label' => '<i class="calendar icon"></i>Erstellt am',
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
$listGenerator->addModal('modal_form_blacklist', [
    'title' => 'Blacklist Eintrag bearbeiten',
    'content' => 'form/f_blacklist.php',
    'size' => 'small',
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Blacklist Eintrag entfernen',
    'content' => 'pages/form_delete.php',
    'size' => 'small',
]);

// Buttons für die Aktionen
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_blacklist',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'id'],
        'conditions' => [
            function ($row) {
                return $row['source'] === 'manual'; // Nur manuelle Einträge können bearbeitet werden
            }
        ]
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui red mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'id']
    ],
];

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Liste generieren
echo '<div align=center><a href="ajax/import_blacklist.php">[ Blacklist importieren ]</a></div>';
echo $listGenerator->generateList();
//Link zu import_blacklist.php



// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<style>
    .ui.mini.label {
        margin: 0;
    }
</style>

<script>
    $(document).ready(function () {
        $('.ui.popup').popup();
    });
</script>