<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) :
    (isset($_POST['content_id']) ? intval($_POST['content_id']) : 0);

if ($content_id === 0) {
    echo "<div class='ui negative message'>
            <div class='header'>Fehler</div>
            <p>Keine Newsletter-ID übermittelt</p>
          </div>";
    exit;
}

// Event-Typen definieren
$eventTypes = [
    'pending' => 'Ausstehend',
    'processing' => 'In Verarbeitung',
    'send' => 'Gesendet',
    'delivered' => 'Zugestellt',
    'open' => 'Geöffnet',
    'click' => 'Geklickt',
    'unsub' => 'Abgemeldet',
    'bounce' => 'Bounce',
    'blocked' => 'Blockiert',
    'spam' => 'Spam',
    'failed' => 'Fehler'
];

// Newsletter-Details laden
$stmt = $db->prepare("
    SELECT 
        subject,
        created_at,
        send_status,
        (SELECT COUNT(DISTINCT recipient_id) FROM email_jobs WHERE content_id = email_contents.id) as total_recipients,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status IN ('send', 'delivered')) as sent_count,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status = 'open') as opened_count,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status = 'click') as clicked_count,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status IN ('failed', 'bounce', 'blocked', 'spam')) as error_count,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status = 'unsub') as unsub_count
    FROM email_contents 
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $content_id, $userId);
$stmt->execute();
$newsletter = $stmt->get_result()->fetch_assoc();

if (!$newsletter) {
    echo "<div class='ui negative message'>
            <div class='header'>Newsletter nicht gefunden</div>
            <p>Der angeforderte Newsletter konnte nicht gefunden werden.</p>
          </div>";
    exit;
}

// Statistiken berechnen
$stats = [
    'sent' => [
        'count' => $newsletter['sent_count'],
        'percent' => $newsletter['total_recipients'] > 0 ?
            round(($newsletter['sent_count'] / $newsletter['total_recipients']) * 100, 1) : 0
    ],
    'opened' => [
        'count' => $newsletter['opened_count'],
        'percent' => $newsletter['total_recipients'] > 0 ?
            round(($newsletter['opened_count'] / $newsletter['total_recipients']) * 100, 1) : 0
    ],
    'clicked' => [
        'count' => $newsletter['clicked_count'],
        'percent' => $newsletter['total_recipients'] > 0 ?
            round(($newsletter['clicked_count'] / $newsletter['total_recipients']) * 100, 1) : 0
    ],
    'errors' => [
        'count' => $newsletter['error_count'],
        'percent' => $newsletter['total_recipients'] > 0 ?
            round(($newsletter['error_count'] / $newsletter['total_recipients']) * 100, 1) : 0
    ],
    'unsub' => [
        'count' => $newsletter['unsub_count'],
        'percent' => $newsletter['total_recipients'] > 0 ?
            round(($newsletter['unsub_count'] / $newsletter['total_recipients']) * 100, 1) : 0
    ]
];

// ListGenerator Config
$listGenerator = new ListGenerator([
    'listId' => 'newsletter_logs',
    'contentId' => 'content_logs',
    'itemsPerPage' => 50,
    'sortColumn' => $_GET['sort'] ?? 'timestamp',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Log-Einträge gefunden.',
    'striped' => true,
    'selectable' => false,
    'celled' => true,
    'tableClasses' => 'ui celled striped small compact table'
]);

// Query für die Log-Daten
$query = "
    SELECT 
        ej.id,
        ej.status as event,
        COALESCE(ej.sent_at, ej.created_at) as timestamp,
        r.email,
        r.first_name,
        r.last_name,
        ej.error_message,
        r.company,
        r.id as recipient_id,
        ej.status as current_status,
        (
            SELECT COUNT(*) 
            FROM email_tracking et 
            WHERE et.job_id = ej.id 
            AND et.event_type IN ('open', 'click')
        ) as total_interactions
    FROM 
        email_jobs ej
        LEFT JOIN recipients r ON ej.recipient_id = r.id
    WHERE 
        ej.content_id = {$content_id}
        GROUP by  ej.id
";

$listGenerator->setSearchableColumns(['r.email', 'r.first_name', 'r.last_name', 'r.company']);
$listGenerator->setDatabase($db, $query, true);
$listGenerator->addFilter('status', 'Status', $eventTypes);

// Header mit Newsletter-Informationen
echo "
<div class='ui segments'>
    <div class='ui blue segment'>
        <h4 class='ui header'>
            <i class='history icon'></i>
            <div class='content'>
                Versandprotokoll: " . htmlspecialchars($newsletter['subject']) . "
                <div class='sub header'>
                    Erstellt am: " . date('d.m.Y H:i', strtotime($newsletter['created_at'])) . " | 
                    Empfänger: " . number_format($newsletter['total_recipients'], 0, ',', '.') . "
                </div>
            </div>
        </h4>
    </div>
    <div class='ui attached segment'>
        <div class='ui tiny statistics'>
            <div class='ui statistic' data-tooltip='Erfolgreich versendet'>
                <div class='value'>" . number_format($stats['sent']['count'], 0, ',', '.') . " <small>(" . $stats['sent']['percent'] . "%)</small></div>
                <div class='label'><i class='paper plane icon'></i> Gesendet</div>
            </div>
            <div class='ui statistic' data-tooltip='Newsletter geöffnet'>
                <div class='value'>" . number_format($stats['opened']['count'], 0, ',', '.') . " <small>(" . $stats['opened']['percent'] . "%)</small></div>
                <div class='label'><i class='eye icon'></i> Geöffnet</div>
            </div>
            <div class='ui statistic' data-tooltip='Links angeklickt'>
                <div class='value'>" . number_format($stats['clicked']['count'], 0, ',', '.') . " <small>(" . $stats['clicked']['percent'] . "%)</small></div>
                <div class='label'><i class='mouse pointer icon'></i> Geklickt</div>
            </div>
            <div class='ui " . ($stats['errors']['count'] > 0 ? "red" : "grey") . " statistic' data-tooltip='Fehler/Bounces'>
                <div class='value'>" . number_format($stats['errors']['count'], 0, ',', '.') . " <small>(" . $stats['errors']['percent'] . "%)</small></div>
                <div class='label'><i class='exclamation circle icon'></i> Fehler</div>
            </div>
            <div class='ui orange statistic' data-tooltip='Abgemeldet'>
                <div class='value'>" . number_format($stats['unsub']['count'], 0, ',', '.') . " <small>(" . $stats['unsub']['percent'] . "%)</small></div>
                <div class='label'><i class='user times icon'></i> Abgemeldet</div>
            </div>
        </div>
    </div>
</div>";

// Spaltendefinitionen
$columns = [
    [
        'name' => 'timestamp',
        'label' => '<i class="clock icon"></i>Zeitpunkt',
        'formatter' => function ($value) {
            return $value ? date('d.m.Y H:i:s', strtotime($value)) : '-';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'email',
        'label' => '<i class="mail icon"></i>E-Mail',
        'formatter' => function ($value) {
            return htmlspecialchars($value ?: '-');
        },
        'allowHtml' => true
    ],
    [
        'name' => 'recipient_info',
        'label' => '<i class="user icon"></i>Empfänger',
        'formatter' => function ($value, $row) {
            $html = "<div>";
            $name = trim($row['first_name'] . ' ' . $row['last_name']);
            if ($name) {
                $html .= "<strong>" . htmlspecialchars($name) . "</strong>";
            }
            if (!empty($row['company'])) {
                $html .= $name ? "<br>" : "";
                $html .= "<span class='ui gray text'>" . htmlspecialchars($row['company']) . "</span>";
            }
            $html .= "</div>";
            return $html ?: '-';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'status_info',
        'label' => '<i class="info circle icon"></i>Status',
        'formatter' => function ($value, $row) use ($eventTypes) {
            $html = "<div class='ui small labels'>";

            $statusColors = [
                'send' => 'blue',
                'delivered' => 'green',
                'failed' => 'red',
                'bounce' => 'red',
                'blocked' => 'red',
                'spam' => 'orange',
                'open' => 'teal',
                'click' => 'teal',
                'unsub' => 'orange',
                'processing' => 'yellow',
                'skipped' => 'grey'
            ];

            $currentStatus = $row['current_status'] ?: 'unknown';
            $color = $statusColors[$currentStatus] ?? 'grey';
            $statusText = $eventTypes[$currentStatus] ?? ucfirst($currentStatus);

            // Skipped oder Fehler Status
            if ($currentStatus === 'skipped' || $currentStatus === 'failed' || $currentStatus === 'bounce' || $currentStatus === 'blocked' || $currentStatus === 'spam') {
                $html .= sprintf(
                    "<div class='ui %s label' data-tooltip='%s' data-position='top left'>%s</div>",
                    $color,
                    htmlspecialchars($row['error_message'] ?: 'Kein Grund angegeben'),
                    $statusText
                );
            } else {
                $html .= "<div class='ui {$color} label'>{$statusText}</div>";
            }

            if ($row['total_interactions'] > 0) {
                $html .= "<div class='ui teal label'>{$row['total_interactions']} Interaktionen</div>";
            }

            $html .= "</div>";

            return $html;
        },
        'allowHtml' => true
    ]
];

foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

echo $listGenerator->generateList();

?>

<style>
    .ui.tiny.statistics {
        margin: 0;
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
    }

    .ui.tiny.statistics .statistic {
        margin: 0.5em;
        min-width: 150px;
        padding: 0.5em;
    }

    .ui.tiny.statistics .statistic .value {
        font-size: 1.5em !important;
    }

    .ui.tiny.statistics .statistic .value small {
        font-size: 0.7em;
        opacity: 0.8;
    }

    .ui.tiny.statistics .statistic .label {
        font-size: 0.9em;
        margin-top: 0.5em;
    }

    .ui.red.tiny.message {
        padding: 0.5em;
        margin-top: 0.5em;
    }
</style>

<script>
    $(document).ready(function () {
        // Tooltips initialisieren
        $('.ui.statistic').popup({
            position: 'top center'
        });

        // Wenn der Newsletter noch versendet wird, regelmäßig aktualisieren
        if (<?php echo $newsletter['send_status'] == 1 ? 'true' : 'false' ?>) {
            setInterval(function () {
                if (typeof standardReloadTable === 'function') {
                    standardReloadTable();
                }
            }, 30000);
        }
    });
</script>