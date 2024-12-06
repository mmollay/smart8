<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

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
    'width' => '1500px',
    'tableClasses' => 'ui celled striped definition small compact table'
];

$listGenerator = new ListGenerator($listConfig);

$query = "
   SELECT DISTINCT
       ec.id as content_id,
       ec.subject,
       ec.send_status,
       CONCAT(s.first_name, ' ', s.last_name) as sender_name,
       s.email as sender_email,
       MAX(ej.sent_at) as send_date,
       (
           SELECT COUNT(DISTINCT r.id)
           FROM recipients r
           JOIN recipient_group rg ON r.id = rg.recipient_id
           JOIN email_content_groups ecg ON rg.group_id = ecg.group_id
           WHERE ecg.email_content_id = ec.id
           AND r.unsubscribed = 0 
           AND r.bounce_status != 'hard'
       ) as potential_recipients,
       COUNT(DISTINCT ej.recipient_id) as total_recipients,
       SUM(CASE WHEN ej.status = 'send' THEN 1 ELSE 0 END) as sent_count,
       SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened_count,
       SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked_count,
       SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END) as failed_count,
       SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count,
       GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR '||') as group_names,
       GROUP_CONCAT(DISTINCT g.color ORDER BY g.name ASC SEPARATOR '||') as group_colors,
       GROUP_CONCAT(DISTINCT g.id) as group_id,
       CASE 
           WHEN ec.send_status = 1 
           AND COUNT(DISTINCT ej.recipient_id) > 0 
           AND COUNT(DISTINCT ej.recipient_id) = 
               SUM(CASE 
                   WHEN ej.status IN ('send', 'failed', 'bounce', 'blocked', 'spam', 'unsub') 
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
   WHERE 
       ec.user_id = '$userId'
   GROUP BY 
       ec.id, 
       ec.subject,
       ec.send_status,
       sender_name,
       sender_email
";

$listGenerator->setSearchableColumns(['ec.subject', 's.first_name', 's.last_name', 's.email', 'g.name']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addFilter('group_id', 'Gruppe', getAllGroups($db));
$newsletterStatus = [
    '0' => 'Nicht gesendet',
    '1' => 'Gesendet/In Versand'
];
$listGenerator->addFilter('send_status', 'Newsletter-Status', $newsletterStatus);

$listGenerator->addExternalButton('new_newsletter', [
    'icon' => 'plus',
    'class' => 'ui primary button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => 'Neuer Newsletter',
    'modalId' => 'modal_form_n',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Newsletter anzulegen']
]);

$listGenerator->addExternalButton('export', [
    'icon' => 'download',
    'class' => 'ui green circular button',
    'position' => 'top',
    'alignment' => 'right',
    'title' => 'CSV Export',
    'onclick' => 'window.location.href="ajax/export.php?type=newsletters&format=csv"'
]);

$columns = [
    [
        'name' => 'content_id',
        'label' => 'ID',
        'width' => '60px'
    ],
    [
        'name' => 'sender_name',
        'width' => '200px',
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
        'formatter' => function ($value) {
            $truncated = mb_strlen($value) > 30 ?
                mb_substr($value, 0, 27) . '...' :
                $value;
            return sprintf(
                '<div class="ui popup-hover" data-content="%s">%s</div>',
                htmlspecialchars($value),
                htmlspecialchars($truncated)
            );
        },
        'allowHtml' => true,
        'width' => '250px'
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
        'name' => 'potential_recipients',
        'label' => '<i class="users icon"></i>Empfänger',
        'formatter' => function ($value, $row) {
            $total = (int) $row['total_recipients'];
            $potential = (int) $value;

            if ($row['send_status'] == 0) {
                return sprintf(
                    '<div class="ui basic blue label" title="Voraussichtliche Empfänger">
                <i class="users icon"></i> %s Empfänger
            </div>',
                    number_format($potential, 0, ',', '.')
                );
            } else {
                return sprintf(
                    '<div class="ui basic label" title="Gesamtempfänger">
                <i class="users icon"></i> %s Gesamt
            </div>',
                    number_format($total, 0, ',', '.')
                );
            }
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
            return "<span class='attachment-info' data-content-id='{$row['content_id']}'></span>";
        },
        'allowHtml' => true,
        'width' => '160px'
    ],
    [
        'name' => 'delivery_stats',
        'label' => '<i class="chart bar icon"></i>Zustellstatistik',
        'formatter' => function ($value, $row) {
            $total = (int) $row['total_recipients'];
            if ($total === 0)
                return '<span class="ui grey text">Keine Empfänger</span>';

            $stats = [];

            $sent = (int) $row['sent_count'];
            if ($sent > 0) {
                $sent_percent = round(($sent / $total) * 100);
                $stats[] = sprintf(
                    '<div class="ui tiny green label" data-tooltip="Versendet">
                       <i class="check icon"></i> %d%% (%d)
                   </div>',
                    $sent_percent,
                    $sent
                );
            }

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
            $sent = (int) $row['sent_count'];
            $failed = (int) $row['failed_count'];

            if ($sent >= $total) {
                $details = [];
                if ($row['sent_count'] > 0)
                    $details[] = "{$row['sent_count']} versendet";
                if ($row['opened_count'] > 0)
                    $details[] = "{$row['opened_count']} geöffnet";
                if ($row['clicked_count'] > 0)
                    $details[] = "{$row['clicked_count']} geklickt";

                return "<div>
                       <span class='ui green text'><i class='check circle icon'></i> Vollständig versendet</span>
                       <div class='ui tiny text'>" . implode(', ', $details) . "</div>
                   </div>";
            }

            if ($failed >= $total) {
                return "<span class='ui red text'><i class='times circle icon'></i> Versand fehlgeschlagen</span>";
            }

            if ($sent > 0) {
                if ($failed > 0) {
                    return "<div>
                           <span class='ui orange text'><i class='exclamation circle icon'></i> Teilweise versendet</span>
                           <div class='ui tiny text'>
                               $sent versendet, $failed fehlgeschlagen
                           </div>
                       </div>";
                }
            }

            return "<div>
                   <span class='ui yellow text'><i class='sync icon'></i> Versand läuft...</span>
                   <div class='ui tiny text'>
                       $sent von $total versendet/interagiert
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
                return $row['send_status'] == 0;
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
        'params' => ['content_id' => 'content_id']
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

foreach ($columns as $column) {
    $listGenerator->addColumn(
        $column['name'],
        $column['label'],
        array_diff_key($column, array_flip(['name', 'label']))
    );
}

foreach ($buttons as $id => $button) {
    $listGenerator->addButton($id, $button);
}

foreach ($modals as $id => $modal) {
    $listGenerator->addModal($id, $modal);
}

$listGenerator->setButtonColumnTitle('left', '', 'left');
$listGenerator->setButtonColumnTitle('right', '', 'right');

echo $listGenerator->generateList();

if (isset($db)) {
    $db->close();
}
?>

<script>
    $(document).ready(function () {
        $('.ui.popup').popup();
        $('.ui.tooltip').popup();
        $('.ui.label').popup();

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

    function sendNewsletter(id) {
        if (confirm('Möchten Sie diesen Newsletter jetzt versenden?')) {
            $.ajax({
                url: 'ajax/send_newsletter.php',
                method: 'POST',
                data: { content_id: id },
                dataType: 'json',
                success: function (response) {
                    console.log('Server response:', response);
                    if (response.success === true) {
                        showSuccessToast(response.message || 'Newsletter wird versendet');
                        setTimeout(function () {
                            if (typeof reloadTable === 'function') {
                                reloadTable();
                            }
                        }, 2100);
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