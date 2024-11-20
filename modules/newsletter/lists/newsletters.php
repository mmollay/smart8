<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'newsletters',
    'contentId' => 'content_newsletters',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? 'content_id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Newsletter gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1400px',
    'tableClasses' => 'ui celled striped definition small compact table'
];

$listGenerator = new ListGenerator($listConfig);

// Optimierte SQL-Abfrage
$query = "
    SELECT DISTINCT
        ec.id as content_id,
        ec.subject,
        ec.send_status,
        CONCAT(s.first_name, ' ', s.last_name) as sender_name,
        s.email as sender_email,
        MAX(ej.sent_at) as send_date,
        COUNT(DISTINCT ej.recipient_id) as total_recipients,
        SUM(CASE WHEN ej.status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN ej.status = 'send' THEN 1 ELSE 0 END) as sent_count,
        SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened_count,
        SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked_count,
        SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END) as failed_count,
        SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR '||') as group_names,
        GROUP_CONCAT(DISTINCT g.color ORDER BY g.name ASC SEPARATOR '||') as group_colors,
        GROUP_CONCAT(DISTINCT g.id) as group_id,  -- Diese Zeile wurde hinzugefügt
        CASE 
            WHEN ec.send_status = 1 
            AND COUNT(DISTINCT ej.recipient_id) > 0 
            AND COUNT(DISTINCT ej.recipient_id) = 
                SUM(CASE 
                    WHEN ej.status IN ('delivered', 'failed', 'bounce', 'blocked', 'spam', 'unsub') 
                    THEN 1 
                    ELSE 0 
                END)
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
        ec.id, 
        ec.subject,
        ec.send_status,
        sender_name,
        sender_email
";

$listGenerator->setSearchableColumns(['ec.subject', 's.first_name', 's.last_name', 's.email', 'g.name']);
$listGenerator->setDatabase($db, $query, true);

// Filter für Gruppen hinzufügen
$listGenerator->addFilter('group_id', 'Gruppe', getAllGroups($db));
$newsletterStatus = [
    '0' => 'Nicht gesendet',
    '1' => 'Gesendet/In Versand'
];
$listGenerator->addFilter('send_status', 'Newsletter-Status', $newsletterStatus);

// Button zum Erstellen eines neuen Newsletters
$listGenerator->addExternalButton('new_newsletter', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => 'Neuer Newsletter',
    'modalId' => 'modal_form_n',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Newsletter anzulegen']
]);


// $listGenerator->addExport([
//     'url' => 'ajax/generic_export.php',
//     'format' => 'csv',
//     //'fields' => ['id', 'first_name', 'last_name'],
//     'title' => 'CSV Export',
//     'popup' => ['content' => 'Liste exportieren'],
//     'beforeExport' => 'function(params) {
//         return confirm("Möchten Sie die Liste exportieren?");
//     }'
// ]);


$listGenerator->addExternalButton('export', [
    'icon' => 'download',
    'class' => 'ui green circular button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => 'CSV Export',
    'onclick' => 'window.location.href="ajax/export.php?type=newsletters&format=csv"'
]);


// Definition der Spalten
$columns = [
    [
        'name' => 'content_id',
        'label' => 'ID',
        'width' => '50px'
    ],
    [
        'name' => 'sender_name',
        'label' => '<i class="user icon"></i>Absender',
        'formatter' => function ($value, $row) {
            return sprintf(
                "<strong>%s</strong><br><small>%s</small>",
                htmlspecialchars($row['sender_name']),
                htmlspecialchars($row['sender_email'])
            );
        },
        'allowHtml' => true
    ],
    [
        'name' => 'subject',
        'label' => '<i class="envelope icon"></i>Betreff',
        'allowHtml' => true
    ],
    [
        'name' => 'group_names',
        'label' => '<i class="tags icon"></i>Gruppen',
        'formatter' => function ($value, $row) {
            if (empty($value))
                return '<span class="ui grey text">Keine Gruppen</span>';

            $groups = explode('||', $value);
            $colors = explode('||', $row['group_colors']);
            $labels = [];

            foreach ($groups as $i => $group) {
                $color = $colors[$i] ?? 'grey';
                $labels[] = sprintf(
                    '<div class="ui mini label %s">%s</div>',
                    htmlspecialchars($color),
                    htmlspecialchars($group)
                );
            }

            return implode(' ', $labels);
        },
        'allowHtml' => true
    ],
    [
        'name' => 'total_recipients',
        'label' => '<i class="users icon"></i>Empfänger',
        'formatter' => function ($value) {
            return number_format($value, 0, ',', '.');
        },
        'allowHtml' => true
    ],
    [
        'name' => 'send_date',
        'label' => '<i class="calendar icon"></i>Gesendet',
        'formatter' => function ($value) {
            return $value ? date('d.m.Y H:i', strtotime($value)) : '<span class="ui grey text">-</span>';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'attachments',
        'label' => '<i class="paperclip icon"></i>Anhänge',
        'formatter' => function ($value, $row) {
            return "<span class='attachment-info' data-content-id='{$row['content_id']}'>Wird geladen...</span>";
        },
        'allowHtml' => true,
        'width' => '150px'
    ],
    [
        'name' => 'delivery_stats',
        'label' => '<i class="chart bar icon"></i>Zustellstatistik',
        'formatter' => function ($value, $row) {
            $total = (int) $row['total_recipients'];
            if ($total === 0)
                return '<span class="ui grey text">Keine Empfänger</span>';

            $stats = [];

            // Versendet (aber noch nicht bestätigt)
            $sent = (int) $row['sent_count'];
            if ($sent > 0) {
                $sent_percent = round(($sent / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny yellow label" data-tooltip="An Mailjet übergeben">
                        <i class="paper plane icon"></i> %d%% (%d)
                    </div>',
                    $sent_percent,
                    $sent
                );
            }

            // Zugestellt (bestätigt)
            $delivered = (int) $row['delivered_count'];
            if ($delivered > 0) {
                $delivered_percent = round(($delivered / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny green label" data-tooltip="Zustellung bestätigt">
                        <i class="check icon"></i> %d%% (%d)
                    </div>',
                    $delivered_percent,
                    $delivered
                );
            }

            // Geöffnet
            $opened = (int) $row['opened_count'];
            if ($opened > 0) {
                $percent = round(($opened / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny blue label" data-tooltip="Newsletter geöffnet">
                        <i class="eye icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $opened
                );
            }

            // Geklickt
            $clicked = (int) $row['clicked_count'];
            if ($clicked > 0) {
                $percent = round(($clicked / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny teal label" data-tooltip="Links angeklickt">
                        <i class="mouse pointer icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $clicked
                );
            }

            // Fehler/Bounces
            $failed = (int) $row['failed_count'];
            if ($failed > 0) {
                $percent = round(($failed / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny red label" data-tooltip="Fehler oder Bounces">
                        <i class="exclamation triangle icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $failed
                );
            }

            // Abgemeldet
            $unsub = (int) $row['unsub_count'];
            if ($unsub > 0) {
                $percent = round(($unsub / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny orange label" data-tooltip="Abgemeldet">
                        <i class="user times icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $unsub
                );
            }

            return empty($stats) ?
                '<span class="ui grey text">Keine Statistiken verfügbar</span>' :
                '<div class="ui labels">' . implode(' ', $stats) . '</div>';
        },
        'allowHtml' => true,
        'width' => '300px'
    ],
    [
        'name' => 'status',
        'label' => 'Status',
        'formatter' => function ($value, $row) {
            if ($row['send_status'] == 0) {
                return "<button class='ui green mini button' onclick='sendNewsletter({$row['content_id']})'><i class='send icon'></i> Senden</button>";
            }

            if ($row['total_recipients'] == 0) {
                return "<span class='ui grey text'>Keine Empfänger</span>";
            }

            $total = (int) $row['total_recipients'];

            // Eine delivered Mail UND eine opened Mail bedeuten 2 verschiedene zugestellte Mails
            $total_delivered = (int) $row['delivered_count'] + (int) $row['opened_count'] + (int) $row['clicked_count'];
            $failed = (int) $row['failed_count'];

            $progress = round(($total_delivered / $total) * 100);

            // Wenn alle Empfänger erreicht wurden
            if ($total_delivered >= $total) {
                $details = [];
                if ($row['delivered_count'] > 0)
                    $details[] = "{$row['delivered_count']} bestätigt";
                if ($row['opened_count'] > 0)
                    $details[] = "{$row['opened_count']} geöffnet";
                if ($row['clicked_count'] > 0)
                    $details[] = "{$row['clicked_count']} geklickt";

                return "<div>
                        <span class='ui green text'><i class='check circle icon'></i> Vollständig zugestellt</span>
                        <div class='ui tiny text'>" . implode(', ', $details) . "</div>
                    </div>";
            }

            if ($failed >= $total) {
                return "<span class='ui red text'><i class='times circle icon'></i> Zustellung fehlgeschlagen</span>";
            }

            if ($total_delivered > 0) {
                if ($failed > 0) {
                    return "<div>
                            <span class='ui orange text'><i class='exclamation circle icon'></i> Teilweise zugestellt</span>
                            <div class='ui tiny text'>
                                $total_delivered zugestellt, $failed fehlgeschlagen
                            </div>
                        </div>";
                }
            }

            return "<div>
                    <span class='ui yellow text'><i class='sync icon'></i> Zustellung läuft...</span>
                    <div class='ui tiny text'>
                        $total_delivered von $total bestätigt/interagiert
                    </div>
                </div>";
        },
        'allowHtml' => true,
        'width' => '180px'
    ]
];

// Definition der Buttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui green mini button',
        'modalId' => 'modal_form_n',
        'popup' => [
            'content' => 'Newsletter bearbeiten',
            'position' => 'top left'
        ],
        'params' => ['update_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['is_fully_sent'] == 0;
            }
        ],
    ],
    'preview' => [
        'icon' => 'eye',
        'position' => 'left',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_preview',
        'popup' => [
            'content' => 'Newsletter-Vorschau anzeigen',
            'position' => 'top left'
        ],
        'params' => ['content_id' => 'content_id']
    ],
    'test' => [
        'icon' => 'paper plane outline',
        'position' => 'left',
        'class' => 'ui orange mini button',
        'popup' => [
            'content' => 'Test-E-Mail an hinterlegte Test-Adresse senden',
            'position' => 'top left'
        ],
        'callback' => 'sendTestMail',
        'params' => ['content_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['is_fully_sent'] == 0;
            }
        ]
    ],
    'log' => [
        'icon' => 'history',
        'position' => 'right',
        'class' => 'ui blue mini button',
        'modalId' => 'modal_log',
        'popup' => [
            'content' => 'Versandprotokoll anzeigen',
            'position' => 'top left'
        ],
        'params' => ['content_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['send_status'] == 1;
            }
        ]
    ],
    'clone' => [
        'icon' => 'copy outline',
        'position' => 'left',
        'class' => 'ui teal mini button',
        'popup' => [
            'content' => 'Newsletter duplizieren und bearbeiten',
            'position' => 'top left'
        ],
        'callback' => 'cloneNewsletter',
        'params' => ['content_id' => 'content_id']
    ],
    'delete' => [
        'icon' => 'trash alternate outline',
        'position' => 'right',
        'class' => 'ui red mini button',
        'modalId' => 'modal_form_delete',
        'popup' => [
            'content' => 'Newsletter löschen',
            'position' => 'top right'
        ],
        'params' => ['delete_id' => 'content_id'],
    ],
];

// Definition der Modals
$modals = [
    'modal_form_n' => [
        'title' => 'Newsletter bearbeiten',
        'content' => 'form/f_newsletters.php',
        'size' => 'large'
    ],
    'modal_form_delete' => [
        'title' => 'Newsletter entfernen',
        'content' => 'pages/form_delete.php',
        'size' => 'small'
    ],
    'modal_preview' => [
        'title' => 'Newsletter Vorschau',
        'content' => 'pages/preview_newsletter.php',
        'size' => 'large'
    ],
    'modal_log' => [
        'title' => 'Versandprotokoll',
        'content' => 'pages/list_logs.php',
        'size' => 'large'
    ]
];


// Spalten zum ListGenerator hinzufügen
foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Buttons zum ListGenerator hinzufügen
foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

// Modals zum ListGenerator hinzufügen
foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
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

// Liste generieren
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}
?>

<script>
    $(document).ready(function () {
        // Initialisiere Semantic UI Komponenten
        $('.ui.popup').popup();
        $('.ui.tooltip').popup();
        $('.ui.label').popup();

        // Anhang-Informationen laden
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

    // Newsletter klonen
    function cloneNewsletter(params) {
        $.ajax({
            url: 'ajax/clone_newsletter.php',
            method: 'POST',
            data: { content_id: params.content_id },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    showSuccessToast(data.message || 'Newsletter erfolgreich dupliziert');
                    if (typeof reloadTable === 'function') {
                        reloadTable();
                    }
                } else {
                    showErrorToast(data.message || 'Fehler beim Duplizieren des Newsletters');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                showErrorToast('Fehler beim Senden der Anfrage: ' + status);
            }
        });
    }

    // Test-Mail senden
    function sendTestMail(params) {
        $.ajax({
            url: 'exec/send_test_mail.php',
            method: 'POST',
            data: { content_id: params.content_id },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    showSuccessToast(data.message || 'Test-Mail wurde gesendet');
                    if (typeof reloadTable === 'function') {
                        reloadTable();
                    }
                } else {
                    showErrorToast(data.message || 'Fehler beim Senden der Test-Mail');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                showErrorToast('Fehler beim Senden der Anfrage: ' + status);
            }
        });
    }

    // Newsletter senden (korrigiert)
    function sendNewsletter(id) {
        if (confirm('Möchten Sie diesen Newsletter jetzt versenden?')) {
            $.ajax({
                url: 'ajax/send_newsletter.php',
                method: 'POST',
                data: { content_id: id },
                dataType: 'json',
                success: function (response) {
                    console.log('Server response:', response); // Debugging
                    if (response.success === true) { // Expliziter Vergleich
                        showSuccessToast(response.message || 'Newsletter wird versendet');
                        // Verzögertes Reload nach Toast
                        setTimeout(function () {
                            if (typeof reloadTable === 'function') {
                                reloadTable();
                            }
                        }, 2100); // Etwas länger als Toast-Anzeigedauer
                    } else {
                        showErrorToast(response.message || 'Fehler beim Versenden');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', { xhr: xhr, status: status, error: error });
                    showErrorToast('Verbindungsfehler: ' + error);
                }
            });
        }
    }


    // Toast-Nachrichten
    function showSuccessToast(message) {
        $('body').toast({
            class: 'success',
            message: message,
            showProgress: 'bottom',
            displayTime: 2000
        });
    }

    function showErrorToast(message) {
        $('body').toast({
            class: 'error',
            message: message,
            showProgress: 'bottom',
            displayTime: 2000
        });
    }
</script>