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
    'width' => '1400px',
    'tableClasses' => 'ui celled striped definition small compact table'
];

$listGenerator = new ListGenerator($listConfig);

$query = "
    SELECT 
        ec.id as content_id, 
        CONCAT(s.first_name, ' ', s.last_name) as sender_name,
        s.email as sender_email, 
        ec.subject, 
        ec.send_status,
        COUNT(DISTINCT ej.recipient_id) as recipients_count,
        SUM(ej.status IN ('success', 'delivered')) as success_count,
        SUM(ej.status IN ('failed', 'bounce', 'blocked')) as failed_count,
        SUM(ej.status IN ('open', 'click')) as engagement_count,
        IFNULL(GROUP_CONCAT(
            DISTINCT CONCAT('<div class=\"ui mini basic compact label ', g.color, '\">', g.name, '</div>')
            SEPARATOR ' '
        ), '<div class=\"ui mini compact label\">Keine Gruppen</div>') as group_labels,
        CASE 
            WHEN ec.send_status = 1 AND COUNT(DISTINCT ej.recipient_id) > 0 AND COUNT(DISTINCT ej.recipient_id) = SUM(ej.status IN ('success', 'delivered', 'failed', 'bounce', 'blocked')) 
            THEN 1 
            ELSE 0 
        END as is_fully_sent
    FROM 
        email_contents ec
        LEFT JOIN senders s ON ec.sender_id = s.id
        LEFT JOIN email_jobs ej ON ec.id = ej.content_id
        LEFT JOIN email_content_groups ecg ON ec.id = ecg.email_content_id
        LEFT JOIN groups g ON ecg.group_id = g.id
    GROUP BY 
        ec.id
";

$listGenerator->setSearchableColumns(['s.first_name', 's.email', 'subject']);
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
    ['name' => 'group_labels', 'label' => '<i class="tags icon"></i>Gruppen', 'allowHtml' => true],
    ['name' => 'recipients_count', 'label' => 'Empfänger'],

    [
        'name' => 'attachments',
        'label' => '<i class="paperclip icon"></i>Anhänge',
        'formatter' => function ($value, $row) {
            return "<span  class='attachment-info' data-content-id='{$row['content_id']}'>Wird geladen...</span>";
        },
        'allowHtml' => true,
        'width' => '150px'
    ],
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
                return "<span class='ui yellow text'>Wird versendet</span>";
            } elseif ($row['send_status'] == 1) {
                return "<span class='ui green text'>Versendet</span>";
            } else {
                return "<span class='ui grey text'>Unbekannt</span>";
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

// Aktualisierte Definition der Aktions-Buttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_form_n',
        'popup' => 'Bearbeiten',
        'params' => ['update_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['is_fully_sent'] == 0;
            }
        ],
    ],
    'clone' => [
        'icon' => 'copy',
        'position' => 'left',
        'class' => 'ui teal mini button',
        'popup' => 'Duplizieren',
        'callback' => 'cloneNewsletter',
        'params' => ['content_id' => 'content_id']
    ],
    'delete' => [
        'icon' => 'trash',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_form_delete',
        'popup' => 'Löschen',
        'params' => ['delete_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['is_fully_sent'] == 0;
            }
        ]
    ],
];

// Hinzufügen der Buttons zum ListGenerator
foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Setzen der Spaltentitel für die Buttons
$listGenerator->setButtonColumnTitle('left', '', 'left');
$listGenerator->setButtonColumnTitle('right', '', 'right');

function getAttachmentInfo($content_id)
{
    $upload_dir = "../../uploads/users/{$content_id}/";
    $files = glob($upload_dir . "*");
    $count = count($files);
    $total_size = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            $total_size += filesize($file);
        }
    }

    return [
        'count' => $count,
        'size' => round($total_size / 1048576, 2) // Konvertierung zu MB
    ];
}

// Generieren und Ausgeben der Liste
echo $listGenerator->generateList();

?>
<script>
    function cloneNewsletter(params) {
        $.ajax({
            url: 'ajax/clone_newsletter.php',
            method: 'POST',
            data: { content_id: params.content_id },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    $('body').toast({
                        message: data.message || 'Newsletter erfolgreich dupliziert.',
                        class: 'success',
                        showProgress: 'bottom'
                    });
                    if (typeof reloadTable === 'function') {
                        reloadTable();
                    } else {
                        console.warn('reloadTable function is not defined');
                    }
                } else {
                    showErrorToast(data.message || 'Fehler beim Duplizieren des Newsletters.');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                showErrorToast('Fehler beim Senden der Anfrage: ' + status);
            }
        });
    }

    function showErrorToast(message) {
        $('body').toast({
            message: message,
            class: 'error',
            showProgress: 'bottom',
            displayTime: 5000
        });
    }
</script>

<script>
    $(document).ready(function () {
        $('.attachment-info').each(function () {
            var $this = $(this);
            var contentId = $this.data('content-id');
            $.ajax({
                url: 'ajax/get_attachment_info.php',
                data: { content_id: contentId },
                success: function (response) {
                    if (response.count > 0) {
                        $this.html(response.count + ' Datei(en) (' + response.size + ' MB)').addClass('ui blue text');
                    } else {
                        $this.html('Keine Anhänge').addClass('ui grey text');
                    }
                },
                error: function () {
                    $this.html('Fehler beim Laden').addClass('ui red text');
                }
            });
        });
    });
</script>

<?php
// Schließen der Datenbankverbindung
if (isset($db)) {
    $db->close();
}