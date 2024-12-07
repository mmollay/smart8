<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

$listConfig = [
    'listId' => 'newsletters',
    'contentId' => 'content_newsletters',
    'itemsPerPage' => 20,
    'sortColumn' => $_GET['sort'] ?? 'ec.id',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Newsletter gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1500px',
];

$listGenerator = new ListGenerator($listConfig);

$query = "
  SELECT DISTINCT
   ec.id content_id,
   ec.subject,
   ec.send_status,
   cs.start_time,
   cs.end_time, 
   cs.processed_emails,
   cs.success_count,
   cs.error_count,
   cs.status as cron_status,
   TIMESTAMPDIFF(SECOND, cs.start_time, cs.end_time) as duration_seconds,
   CONCAT(s.first_name, ' ', s.last_name) as sender_name,
   s.email as sender_email,
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
   LEFT JOIN cron_status cs ON cs.content_id = ec.id
    AND cs.id = (
        SELECT cs2.id 
        FROM cron_status cs2 
        WHERE cs2.content_id = ec.id
        AND cs2.status = 'completed' 
        AND cs2.end_time IS NOT NULL 
        ORDER BY cs2.end_time DESC 
        LIMIT 1
    )
WHERE 
   ec.user_id = '$userId' 
GROUP BY 
   ec.id, 
   ec.subject,
   ec.send_status,
   sender_name,
   sender_email,
   cs.start_time,
   cs.end_time,
   cs.processed_emails,
   cs.success_count,
   cs.error_count,
   cs.status 
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
    'modalId' => 'modal_edit',
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
        'formatter' => function ($value, $row) {
            $truncated = mb_strlen($value) > 30 ?
                mb_substr($value, 0, 27) . '...' :
                $value;

            $html = sprintf(
                '<div class="ui popup-hover" data-content="%s">%s</div>',
                htmlspecialchars($value),
                htmlspecialchars($truncated)
            );

            // Attachment-Info direkt danach
            $html .= " <span class='attachment-info' data-content-id='{$row['content_id']}'></span>";

            return $html;
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
        'allowHtml' => true,
        'width' => '200px'
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
        'allowHtml' => true,
        'width' => '250px'
    ],
    [
        'name' => 'timing',
        'label' => '<i class="clock outline icon"></i>Zeit',
        'formatter' => function ($value, $row) {
            // Wenn noch nicht gestartet
            if (!$row['start_time']) {
                return '<span class="ui grey text">-</span>';
            }

            $date = date('d.m.Y', strtotime($row['start_time']));
            $startTime = date('H:i', strtotime($row['start_time']));

            // Wenn noch läuft
            if ($row['cron_status'] === 'running') {
                return sprintf(
                    '<div data-tooltip="%s" data-position="right center" class="ui small text">%s<br>%s - <i class="spinner loading icon"></i></div>',
                    'Start: ' . date('d.m.Y H:i:s', strtotime($row['start_time'])),
                    $date,
                    $startTime
                );
            }

            // Wenn abgeschlossen
            if ($row['end_time']) {
                $endTime = date('H:i', strtotime($row['end_time']));
                $seconds = $row['duration_seconds'];

                // Kurze Dauer
                $duration = '';
                if ($seconds < 60) {
                    $duration = $seconds . 's';
                } elseif ($seconds < 3600) {
                    $duration = sprintf('%dm', floor($seconds / 60));
                } else {
                    $duration = sprintf('%dh%d', floor($seconds / 3600), floor(($seconds % 3600) / 60));
                }

                // Details für Tooltip
                $details = sprintf(
                    "
                    Start: %s<br>
                    Ende: %s<br>
                    Dauer: %s",
                    date('d.m.Y H:i:s', strtotime($row['start_time'])),
                    date('d.m.Y H:i:s', strtotime($row['end_time'])),
                    $seconds < 60 ? "$seconds Sekunden" :
                    ($seconds < 3600 ? sprintf('%d Minuten %d Sekunden', floor($seconds / 60), $seconds % 60) :
                        sprintf('%d Stunden %d Minuten', floor($seconds / 3600), floor(($seconds % 3600) / 60)))
                );

                return sprintf(
                    '<div data-html="%s" class="ui popup2 small text">%s<br>%s - %s (%s)</div>',
                    htmlspecialchars($details),
                    $date,
                    $startTime,
                    $endTime,
                    $duration
                );
            }
        },
        'allowHtml' => true,
        'width' => '200px'
    ],
    [
        'name' => 'delivery_stats',
        'label' => '<i class="chart bar icon"></i>Zustellstatistik',
        'formatter' => function ($value, $row) {
            $total = (int) $row['total_recipients'];
            if ($total === 0) {
                return '<span class="ui grey text"></span>';
            }

            $stats = [];

            // Basis-Zahlen
            $unsub = (int) $row['unsub_count'];
            $clicked = (int) $row['clicked_count'];
            $opened = (int) $row['opened_count'];
            $sent = (int) $row['sent_count'] + $opened + $clicked + $unsub;
            $failed = (int) $row['failed_count'];

            // Öffnungen inkl. Klicks berechnen
            $total_opened = $opened + $clicked;

            // HTML-Container für dynamische Updates
            $html = "<div data-stats-id='{$row['content_id']}'>";

            // Versand-Statistik
            if ($sent > 0) {
                $sent_percent = min(100, round(($sent / $total) * 100));
                $stats[] = sprintf(
                    '<div class="ui tiny gray label" data-tooltip="Versendet">
                        <i class="check icon"></i> %d%% (%d)
                    </div>',
                    $sent_percent,
                    $sent
                );
            }

            // Öffnungs-Statistik (inkl. Klicks)
            if ($total_opened > 0) {
                $percent = min(100, round(($total_opened / $total) * 100));
                $stats[] = sprintf(
                    '<div class="ui tiny blue label" data-tooltip="Newsletter geöffnet (inkl. Klicks)">
                        <i class="eye icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $total_opened
                );
            }

            // Klick-Statistik
            if ($clicked > 0) {
                $percent = min(100, round(($clicked / $total) * 100));
                $stats[] = sprintf(
                    '<div class="ui tiny teal label" data-tooltip="Links angeklickt">
                        <i class="mouse pointer icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $clicked
                );
            }

            // Abmeldungs-Statistik
            if ($unsub > 0) {
                $percent = min(100, round(($unsub / $total) * 100));
                $stats[] = sprintf(
                    '<div class="ui tiny orange label" data-tooltip="Abgemeldet">
                        <i class="user times icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $unsub
                );
            }

            // Fehler-Statistik
            if ($failed > 0) {
                $percent = min(100, round(($failed / $total) * 100));
                $stats[] = sprintf(
                    '<div class="ui tiny red label" data-tooltip="Fehler oder Bounces">
                        <i class="exclamation triangle icon"></i> %d%% (%d)
                    </div>',
                    $percent,
                    $failed
                );
            }

            $html .= empty($stats)
                ? '<span class="ui grey text">Keine Statistiken verfügbar</span>'
                : '<div class="ui labels">' . implode(' ', $stats) . '</div>';

            $html .= "</div>";

            return $html;
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

            $unsub = (int) $row['unsub_count'];
            $total = (int) $row['total_recipients'];
            $clicked = (int) $row['clicked_count'];
            $opened = (int) $row['opened_count'] + $clicked;
            $sent = (int) $row['sent_count'] + $opened + $clicked + $unsub;
            $failed = (int) $row['failed_count'];

            // Berechne den Fortschritt
            $progress = round(($sent / $total) * 100);

            if ($failed >= $total) {
                return "<span class='ui red text'><i class='times circle icon'></i> Versand fehlgeschlagen</span>";
            }

            if ($sent >= $total) {
                $detailsHtml = !empty($details) ? '<div class="ui tiny text">' . implode(' | ', $details) . '</div>' : '';

                return "
                <div>
                    <span class='ui green text'><i class='check circle icon'></i> Vollständig versendet</span>
                    {$detailsHtml}
                </div>";
            }

            // Progress Bar für laufende Sendungen
            $details = [];
            if ($unsub > 0) {
                $details[] = "<span class='ui orange text'>{$unsub} Abmeldungen</span>";
            }
            if ($failed > 0) {
                $details[] = "<span class='ui red text'>{$failed} Fehler</span>";
            }

            $detailsHtml = !empty($details) ? '<div class="ui tiny text">' . implode(' | ', $details) . '</div>' : '';

            return "
            <div>
                <div class='ui tiny active progress' data-content-id='{$row['content_id']}' data-percent='{$progress}'>
                    <div class='bar' style='width: {$progress}%'></div>
                    <div class='label'>{$sent} von {$total} versendet</div>
                </div>
                {$detailsHtml}
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
        'modalId' => 'modal_edit',
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
    'modal_edit' => [
        'title' => 'Newsletter bearbeiten',
        'content' => 'form/f_newsletters.php',
        'class' => 'large'
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
<!-- Dann unsere Attachment-Funktionalität -->
<script src="js/newsletter-attachment.js"></script>

<script>
    // Die Komponenten-Initialisierung
    function initializeComponents() {
        $('.ui.popup').popup();
        $('.ui.tooltip').popup();
        $('.ui.label').popup();
        $('.ui.progress').progress({
            precision: 1,
            showActivity: false
        });
        // Für die Zeitanzeige
        $('.popup2').popup({
            html: true,
        });
    }

    // Wenn das Dokument geladen ist
    $(document).ready(function () {
        initializeComponents();
    });
</script>