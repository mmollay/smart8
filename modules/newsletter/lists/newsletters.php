<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'newsletters',
    'contentId' => 'content_newsletters',
    'itemsPerPage' => 10,
    'sortColumn' => $_GET['sort'] ?? 'ec.created_at',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1200px',
    'tableClasses' => 'ui celled striped definition small compact table',
];

$listGenerator = new ListGenerator($listConfig);

// Datenbank-Abfrage für Newsletter-Daten
$query = "
    SELECT 
        ec.id as content_id, 
        CONCAT(s.first_name, ' ', s.last_name) as sender_name,
        s.email as sender_email, 
        ec.subject, 
        ec.send_status,
        COUNT(ej.recipient_id) as recipients_count,
        SUM(ej.status IN ('success', 'delivered')) as success_count,
        SUM(ej.status IN ('failed', 'bounce', 'blocked')) as failed_count,
        SUM(ej.status IN ('open', 'click')) as engagement_count
    FROM 
        email_contents ec
        LEFT JOIN senders s ON ec.sender_id = s.id
        LEFT JOIN email_jobs ej ON ec.id = ej.content_id
    GROUP BY 
        ec.id
";

$listGenerator->setSearchableColumns(['sender_name', 'sender_email', 'subject']);
$listGenerator->setDatabase($db, $query, true);

// Button zum Erstellen eines neuen Newsletters
$listGenerator->addExternalButton('new_newsletter', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'inline',
    'alignment' => '',
    'title' => 'Neuer Newsletter',
    'modalId' => 'modal_form_n',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Newsletter anzulegen']
]);

// Definition der Tabellenspalten
$columns = [
    ['name' => 'content_id', 'label' => 'ID'],
    ['name' => 'sender_name', 'label' => '<i class="user icon"></i>Absender'],
    ['name' => 'sender_email', 'label' => '<i class="mail icon"></i>E-Mail'],
    ['name' => 'subject', 'label' => '<i class="envelope icon"></i>Betreff'],
    ['name' => 'recipients_count', 'label' => 'Empfänger'],
    [
        'name' => 'success_count',
        'label' => 'Erfolgreich',
        'formatter' => function ($value) {
            return "<span class='ui green text'>{$value}</span>";
        },
        'allowHtml' => true
    ],
    [
        'name' => 'failed_count',
        'label' => 'Fehlgeschlagen',
        'formatter' => function ($value) {
            return "<span class='ui red text'>{$value}</span>";
        },
        'allowHtml' => true
    ],
    [
        'name' => 'engagement_count',
        'label' => 'Interaktionen',
        'formatter' => function ($value) {
            return "<span class='ui blue text'>{$value}</span>";
        },
        'allowHtml' => true
    ],
    [
        'name' => 'actions',
        'label' => 'Status',
        'formatter' => function ($value, $row) {
            if ($row['send_status'] == 0) {
                return "<button class='ui green mini button' onclick='sendNewsletter({$row['content_id']})'><i class='send icon'></i> Senden</button>";
            } elseif ($row['send_status'] == 1 && $row['recipients_count'] > ($row['success_count'] + $row['failed_count'])) {
                return "<div class='ui yellow mini label'>Wird versendet</div>";
            } elseif ($row['send_status'] == 1) {
                return "<div class='ui green mini label'>Versendet</div>";
            } else {
                return "<div class='ui grey mini label'>Unbekannt</div>";
            }
        },
        'allowHtml' => true
    ],
];

// Hinzufügen der Spalten zum ListGenerator
foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Definition der Modals
$modals = [
    'modal_form_n' => ['title' => 'Newsletter bearbeiten', 'content' => 'form/f_newsletters.php', 'size' => 'large'],
    'modal_form_delete' => ['title' => 'Newsletter entfernen', 'content' => 'pages/form_delete.php', 'size' => 'small'],
];

// Hinzufügen der Modals zum ListGenerator
foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

// Definition der Aktions-Buttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_n',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'content_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'content_id']
    ],
];

// Hinzufügen der Buttons zum ListGenerator
foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'center');
$listGenerator->setButtonColumnTitle('right', '', 'right');

// Generieren und Ausgeben der Liste
echo $listGenerator->generateList();

// Schließen der Datenbankverbindung
if (isset($db)) {
    $db->close();
}