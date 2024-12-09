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
        AND NOT EXISTS (
            SELECT 1 
            FROM blacklist b 
            WHERE b.email = r.email 
            AND b.user_id = r.user_id
        )
    ) as potential_recipients,
    COUNT(DISTINCT ej.recipient_id) as total_recipients,
    SUM(CASE WHEN ej.status = 'send' THEN 1 ELSE 0 END) as sent_count,
    SUM(CASE WHEN ej.status = 'open' THEN 1 ELSE 0 END) as opened_count,
    SUM(CASE WHEN ej.status = 'click' THEN 1 ELSE 0 END) as clicked_count,
    SUM(CASE WHEN ej.status IN ('failed', 'bounce', 'blocked', 'spam') THEN 1 ELSE 0 END) as failed_count,
    SUM(CASE WHEN ej.status = 'unsub' THEN 1 ELSE 0 END) as unsub_count,
    SUM(CASE WHEN ej.status = 'skipped' AND ej.error_message LIKE '%Blacklist%' THEN 1 ELSE 0 END) as blacklisted_count,
    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name ASC SEPARATOR '||') as group_names,
    GROUP_CONCAT(DISTINCT g.color ORDER BY g.name ASC SEPARATOR '||') as group_colors,
    GROUP_CONCAT(DISTINCT g.id) as group_id
FROM email_contents ec
LEFT JOIN senders s ON ec.sender_id = s.id AND s.id IS NOT NULL
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
WHERE ec.user_id = '$userId'
GROUP BY 
    ec.id, 
    ec.subject,
    ec.send_status,
    s.first_name,
    s.last_name,
    s.email,
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
    'position' => 'inline',
    'alignment' => 'right',
    'title' => 'Neuer Newsletter',
    'modalId' => 'modal_edit',
    'popup' => ['content' => 'Klicken Sie hier, um einen neuen Newsletter anzulegen']
]);

// $listGenerator->addExternalButton('export', [
//     'icon' => 'download',
//     'class' => 'ui green circular button',
//     'position' => 'top',
//     'alignment' => 'right',
//     'title' => 'CSV Export',
//     'onclick' => 'window.location.href="ajax/export.php?type=newsletters&format=csv"'
// ]);

$columns = [
    [
        'name' => 'content_id',
        'label' => 'ID',
        'width' => '60px'
    ],
    [
        'name' => 'timing',
        'label' => '<i class="clock outline icon"></i>Zeit',
        'formatter' => function ($value, $row) {
            // Wenn noch nicht gestartet
            // if (!$row['start_time']) {
            //     return '<span class="ui grey text">-</span>';
            // }
        
            if ($row['send_status'] == 0 && $row['potential_recipients'] > 0)
                return "<button class='ui green mini button' onclick='sendNewsletter({$row['content_id']})'><i class='send icon'></i> Senden</button>";

            if ($row['send_status'] == 0 && !$row['potential_recipients']) {
                return sprintf(
                    '<button data-modal="modal_edit" 
                                data-content="Newsletter bearbeiten" 
                                data-position="top left" 
                                data-listid="newsletters" 
                                data-update_id="%d" 
                                class="ui blue mini button">
                            <i class="edit icon"></i>
                            Bearbeiten
                        </button>',
                    $row['content_id']
                );
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
                    '<div data-html="%s" class="ui popup_hover small text">%s<br>%s - %s (%s)</div>',
                    htmlspecialchars($details),
                    $date,
                    $startTime,
                    $endTime,
                    $duration
                );
            }
        },
        'allowHtml' => true,
    ],
    [
        'name' => 'sender_email',
        'label' => '<i class="user icon"></i>Absender',
        'formatter' => function ($value, $row) {
            if (empty($value)) {
                return '<span class="ui grey text">-</span>';
            }
            return htmlspecialchars($value);
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
                '<div class="ui popup-hover" data-html="%s">%s</div>',
                htmlspecialchars($value),
                htmlspecialchars($truncated)
            );

            // Attachment-Info direkt danach
            $html .= " <span class='attachment-info' data-content-id='{$row['content_id']}'></span>";

            return $html;
        },
        'allowHtml' => true,
        'width' => '240px'
    ],
    [
        'name' => 'group_names',
        'label' => 'Gruppen',
        'formatter' => function ($value, $row) {
            if (empty($value)) {
                return '<span class="ui grey text">-</span>';
            }

            $groups = explode('||', $value);
            $colors = explode('||', $row['group_colors']);

            // Icons für die Anzeige
            $icons = [];
            $groupLabels = [];

            foreach ($groups as $i => $group) {
                $color = $colors[$i] ?? 'grey';
                // Icon
                $icons[] = sprintf(
                    '<i class="tags icon %s"></i>',
                    htmlspecialchars($color)
                );
                // Label mit Zeilenumbruch
                $groupLabels[] = sprintf(
                    '<div class="ui label %s" style="display: block; margin-bottom: 4px;">%s</div>',
                    htmlspecialchars($color),
                    htmlspecialchars($group)
                );
            }

            // HTML für Popup sicher kodieren
            $popupContent = htmlspecialchars(implode('', $groupLabels));

            return sprintf(
                '<div class="ui popup-hover" data-html=\'%s\'>
                    %s
                </div>',
                $popupContent,
                implode(' ', $icons)
            );
        },
        'allowHtml' => true,
    ],
    [
        'name' => 'potential_recipients',
        'label' => 'Empfänger',
        'formatter' => function ($value, $row) {
            // Zahlen formatieren (z.B. 1.234 statt 1234)
            $anzahl = $row['send_status'] == 0
                ? number_format((int) $value, 0, ',', '.')
                : number_format((int) $row['total_recipients'], 0, ',', '.');

            // Art der Empfänger bestimmen
            $titel = $row['send_status'] == 0
                ? 'Voraussichtliche Empfänger'
                : 'Gesamtempfänger';

            return "<div class='ui basic label' title='$titel'>$anzahl</div>";
        },
        'allowHtml' => true,
    ],
    [
        'name' => 'delivery_stats',
        'label' => '<i class="chart bar icon"></i>Statistik',
        'formatter' => function ($value, $row) {
            $total = (int) $row['total_recipients'];
            if ($total === 0)
                return '<span class="ui grey text"></span>';

            $stats = [];

            // Statistiken sammeln mit primärem Fokus auf absolute Zahlen
            $mapping = [
                ['blacklisted_count', 'ban', 'black', 'Auf Blacklist'],
                ['sent_count', 'paper plane', 'yellow', 'Versendet'],
                ['opened_count', 'eye', 'blue', 'Geöffnet'],
                ['clicked_count', 'mouse pointer', 'teal', 'Geklickt'],
                ['failed_count', 'exclamation triangle', 'red', 'Fehler'],
                ['unsub_count', 'user times', 'orange', 'Abgemeldet']
            ];

            foreach ($mapping as [$key, $icon, $color, $tooltip]) {
                $count = (int) $row[$key];
                if ($count > 0) {
                    $percent = round(($count / $total) * 100);
                    $stats[] = sprintf(
                        '<div class="ui tiny %s label" data-tooltip="%s">
                            <i class="%s icon"></i>%d <small><br>(%d%%)</small>
                        </div>',
                        $color,
                        $tooltip,
                        $icon,
                        $count,
                        $percent
                    );
                }
            }

            return empty($stats)
                ? '<span class="ui grey text">-</span>'
                : '<div class="ui small labels">' . implode(' ', $stats) . '</div>';
        },
        'allowHtml' => true,
        'width' => '200px'
    ],
    [
        'name' => 'status',
        'label' => 'Status',
        'formatter' => function ($value, $row) {
            // Status: Keine Empfänger gewählt
            if (empty($row['group_names'])) {
                return "<span class='ui grey text'>
                        <i class='users slash icon'></i> Keine Empfänger gewählt
                       </span>";
            }

            // Status: Kein Absender gewählt
            if (empty($row['sender_email'])) {
                return "<span class='ui orange text'>
                        <i class='user slash icon'></i> Kein Absender gewählt
                       </span>";
            }

            // Status: Versendebereit
            if ($row['send_status'] == 0 && !empty($row['group_names']) && !empty($row['sender_email'])) {
                return "<span class='ui blue text'>
                        <i class='paper plane icon'></i> Versendebereit
                       </span>";
            }

            // Versandstatistiken berechnen
            $total = (int) $row['total_recipients'];
            $blacklisted = (int) $row['blacklisted_count'];
            $clicked = (int) $row['clicked_count'];
            $opened = (int) $row['opened_count'];
            $sent = (int) $row['sent_count'] + $opened + $clicked;
            $failed = (int) $row['failed_count'];

            // Gesamtfortschritt
            $verarbeitet = $sent + $failed + $blacklisted;
            $progress = round(($verarbeitet / $total) * 100);

            // Status: Versand läuft
            if ($verarbeitet < $total) {
                return "<div class='ui tiny active progress'
                            data-content-id='{$row['content_id']}'
                            data-percent='{$progress}'>
                            <div class='bar' style='width: {$progress}%'></div>
                            <div class='label'>
                                <i class='sync loading icon'></i> 
                                {$sent} von {$total} versendet
                            </div>
                        </div>";
            }

            // Status: Erfolgreich versendet
            //if ($failed == 0 && $blacklisted == 0) {
            return "<span class='ui green text'>
                        <i class='check circle icon'></i> Erfolgreich versendet
                       </span>";
            //}
        
            // Status: Mit Fehlern versendet
            // return "<span class='ui yellow text'>
            //         <i class='exclamation circle icon'></i> Versendet mit {$failed} Fehlern
            //        </span>";
        },
        'allowHtml' => true,
        'width' => '220px'
    ]
];

// Definition der Buttons
$buttons = [
    'edit' => [
        'icon' => 'edit',
        'position' => 'left',
        'class' => 'ui blue mini button',
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
    'clone' => [
        'icon' => 'copy outline',
        'position' => 'left',
        'class' => 'ui mini button',
        'popup' => [
            'content' => 'Newsletter duplizieren und bearbeiten',
            'position' => 'top left'
        ],
        'callback' => 'cloneNewsletter',
        'params' => ['content_id' => 'content_id']
    ],
    'preview' => [
        'icon' => 'eye',
        'position' => 'left',
        'class' => 'ui mini button',
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
        'class' => 'ui mini button',
        'popup' => [
            'content' => 'Test-E-Mail an hinterlegte Test-Adresse senden',
            'position' => 'top left'
        ],
        'callback' => 'sendTestMail',
        'params' => ['content_id' => 'content_id'],
        'conditions' => [
            function ($row) {
                return $row['send_status'] == 0;  // Nur anzeigen wenn noch nicht versendet
            }
        ]
    ],
    'log' => [
        'icon' => 'history',
        'position' => 'right',
        'class' => 'ui mini button',
        'modalId' => 'modal_log',
        'popup' => [
            'content' => 'Versandprotokoll anzeigen',
            'position' => 'top left'
        ],
        'conditions' => [
            function ($row) {
                return $row['send_status'] != 0;  // Nur anzeigen wenn noch nicht versendet
            }
        ],
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
        'conditions' => [
            function ($row) {
                return $row['send_status'] == 0;  // Nur anzeigen wenn noch nicht versendet
            }
        ],
    ],
];

$modals = [
    'modal_edit' =>
        [
            'title' => 'Newsletter bearbeiten',
            'content' => 'form/f_newsletters.php',
            'size' => 'fullscreen overlay',  // statt 'class' nutzen wir jetzt 'size'
            'method' => 'POST',
            'scrolling' => true,
            'buttons' => [
                'approve' => [
                    'text' => 'Speichern',
                    'class' => 'orange',
                    'icon' => 'check',
                    'action' => 'submit',
                    //'onclick' => "alert('test')",  // Optional: wenn du einen Alert haben möchtest
                    'form_id' => 'form_edit'  // Hier die ID deines Formulars eintragen
                ],
                'cancel' => [
                    'text' => 'Abbrechen',
                    'class' => 'cancel',
                    'icon' => 'times',
                    'action' => 'close'
                ]
            ]
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
        $('.popup-hover').popup({
            html: true,
        });
    }

    // Wenn das Dokument geladen ist
    $(document).ready(function () {
        initializeComponents();
    });
</script>