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
]);

// Erweiterte Datenbank-Abfrage mit aktiven und abgemeldeten Empfängern
$query = "
    SELECT 
        g.id as group_id, is_temp,
        CONCAT('<div class=\"ui ', g.color, ' compact empty mini circular label\"></div> ', g.name) as group_name,
        COUNT(DISTINCT r.id) as total_recipients,
        COUNT(DISTINCT CASE WHEN r.unsubscribed = 0 THEN r.id END) as active_recipients,
        COUNT(DISTINCT CASE WHEN r.unsubscribed = 1 THEN r.id END) as unsubscribed_recipients,
        g.created_at
    FROM 
        groups g
        LEFT JOIN recipient_group rg ON g.id = rg.group_id
        LEFT JOIN recipients r ON rg.recipient_id = r.id
    WHERE 
        g.user_id = '$userId'

    GROUP BY 
        g.id
";

$listGenerator->setSearchableColumns(['g.name']);
$listGenerator->setDatabase($db, $query, true, "s", [$userId]);

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

$listGenerator->addFilter('is_temp', 'Gruppen-Typ', [
    '' => 'Alle Gruppen',
    '0' => 'Reguläre Gruppen',
    '1' => 'Temporäre Gruppen'
], [
    'defaultValue' => '0'  // Setzt "Reguläre Gruppen" als Standard
]);


// $listGenerator->addExport([
//     'url' => 'ajax/generic_export.php',
//     'listId' => 'groups',
//     'format' => 'csv',
//     'fields' => [
//         'group_id',
//         'group_name',
//         'total_recipients',
//         'active_recipients',
//         'unsubscribed_recipients',
//         'created_at'
//     ],
//     'title' => 'Gruppen Export',
//     'popup' => ['content' => 'Gruppenliste exportieren']
// ]);

// $listGenerator->addExternalButton('export', [
//     'icon' => 'download',
//     'class' => 'ui green button',
//     'position' => 'top',
//     'alignment' => 'right',
//     'title' => 'CSV Export',
//     'onclick' => 'window.location.href="ajax/export.php?type=groups&format=csv"'
// ]);

// Spalten definieren
$listGenerator->addColumn('group_id', 'ID');
$listGenerator->addColumn('group_name', '<i class="users icon"></i>Gruppe', ['allowHtml' => true]);

// Neue kombinierte Empfänger-Spalte mit Details
$listGenerator->addColumn('recipients_details', '<i class="user icon"></i>Empfänger', [
    'formatter' => function ($value, $row) {
        $total = (int) $row['total_recipients'];
        $active = (int) $row['active_recipients'];
        $unsubscribed = (int) $row['unsubscribed_recipients'];

        if ($total === 0) {
            return '<div class="ui grey text">Keine Empfänger</div>';
        }

        $html = '<div class="ui small labels">';

        // Aktive Empfänger
        if ($active > 0) {
            $html .= sprintf(
                '<div class="ui green label" title="Aktive Empfänger">
                    <i class="user check icon"></i>
                    %d aktiv
                </div>',
                $active
            );
        }

        // Abgemeldete Empfänger
        if ($unsubscribed > 0) {
            $html .= sprintf(
                '<div class="ui grey label" title="Abgemeldete Empfänger">
                    <i class="user times icon"></i>
                    %d abgemeldet
                </div>',
                $unsubscribed
            );
        }

        $html .= '</div>';

        return $html;
    },
    'allowHtml' => true
]);

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

// Setzen der Spaltentitel und Ausrichtung für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Generiere und gib die Liste aus
echo $listGenerator->generateList();

// Schließe die Datenbankverbindung
if (isset($db)) {
    $db->close();
}
?>

<style>
    .ui.small.labels {
        display: flex;
        gap: 0.5em;
        align-items: center;
    }

    .ui.small.labels .label {
        margin: 0 !important;
    }
</style>