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

// Status-Zählung aus der Datenbank holen
$statusCountQuery = "
    SELECT 
        ej.status,
        COUNT(*) as count
    FROM 
        email_jobs ej
    WHERE 
        ej.content_id = ?
    GROUP BY 
        ej.status";

$stmt = $db->prepare($statusCountQuery);
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result();

$statusCounts = [];
while ($row = $result->fetch_assoc()) {
    $statusCounts[$row['status']] = $row['count'];
}

// Status-Config mit Farben und Icons
$statusConfig = [
    'pending' => ['color' => 'grey', 'icon' => 'clock', 'label' => 'Ausstehend'],
    'processing' => ['color' => 'yellow', 'icon' => 'sync', 'label' => 'In Verarbeitung'],
    'send' => ['color' => 'blue', 'icon' => 'paper plane', 'label' => 'Gesendet'],
    'open' => ['color' => 'teal', 'icon' => 'eye', 'label' => 'Geöffnet'],
    'click' => ['color' => 'teal', 'icon' => 'mouse pointer', 'label' => 'Geklickt'],
    'unsub' => ['color' => 'orange', 'icon' => 'user times', 'label' => 'Abgemeldet'],
    'bounce' => ['color' => 'red', 'icon' => 'mail reply', 'label' => 'Bounce'],
    'blocked' => ['color' => 'red', 'icon' => 'ban', 'label' => 'Blockiert'],
    'spam' => ['color' => 'orange', 'icon' => 'exclamation triangle', 'label' => 'Spam'],
    'failed' => ['color' => 'red', 'icon' => 'times circle', 'label' => 'Fehler']
];

// Event-Typen mit Anzahl, Farbe und Icon definieren
$eventTypes = [];
foreach ($statusConfig as $status => $config) {
    $count = $statusCounts[$status] ?? 0;
    $eventTypes[$status] = sprintf(
        "<i class='%s icon' style='color: var(--%s)'></i>%s (%d)",
        $config['icon'],
        $config['color'],
        $config['label'],
        $count
    );
}

// Newsletter-Details laden
$stmt = $db->prepare("
    SELECT
        subject,
        created_at,
        send_status,
        (SELECT COUNT(DISTINCT recipient_id) FROM email_jobs WHERE content_id = email_contents.id) as total_recipients,
        (SELECT COUNT(*) FROM email_jobs WHERE content_id = email_contents.id AND status = 'send') as sent_count,
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
    'itemsPerPage' => 20,
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
    GROUP BY 
        ej.id
";

$listGenerator->setSearchableColumns(['r.email', 'r.first_name', 'r.last_name', 'r.company']);
$listGenerator->setDatabase($db, $query, true);

// Filter mit Standardwert hinzufügen
$listGenerator->addFilter('status', '<i class="filter icon"></i>Status Filter', $eventTypes, [
    'defaultValue' => 'send',
    'placeholder' => 'Alle Status anzeigen',
    'allowHtml' => true,
    // 'customClass' => 'status-filter'
]);

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
            <div class='ui blue statistic' data-tooltip='Erfolgreich versendet'>
                <div class='value'>" . number_format($stats['sent']['count'], 0, ',', '.') . " <small>(" . $stats['sent']['percent'] . "%)</small></div>
                <div class='label'><i class='paper plane icon'></i> Gesendet</div>
            </div>
            <div class='ui teal statistic' data-tooltip='Newsletter geöffnet'>
                <div class='value'>" . number_format($stats['opened']['count'], 0, ',', '.') . " <small>(" . $stats['opened']['percent'] . "%)</small></div>
                <div class='label'><i class='eye icon'></i> Geöffnet</div>
            </div>
            <div class='ui teal statistic' data-tooltip='Links angeklickt'>
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
        'formatter' => function ($value, $row) use ($statusConfig) {
            $html = "<div class='ui small labels'>";

            $currentStatus = $row['current_status'] ?: 'unknown';
            $config = $statusConfig[$currentStatus] ?? ['color' => 'grey', 'icon' => 'question', 'label' => ucfirst($currentStatus)];

            // Status Label
            if (in_array($currentStatus, ['skipped', 'failed', 'bounce', 'blocked', 'spam'])) {
                $html .= sprintf(
                    "<div class='ui %s label' data-tooltip='%s' data-position='top left'>
                        <i class='%s icon'></i> %s
                    </div>",
                    $config['color'],
                    htmlspecialchars($row['error_message'] ?: 'Kein Grund angegeben'),
                    $config['icon'],
                    $config['label']
                );
            } else {
                $html .= sprintf(
                    "<div class='ui %s label'>
                        <i class='%s icon'></i> %s
                    </div>",
                    $config['color'],
                    $config['icon'],
                    $config['label']
                );
            }

            // Interaktionen Label
            if ($row['total_interactions'] > 0) {
                $html .= sprintf(
                    "<div class='ui teal label'>
                        <i class='mouse pointer icon'></i> %d Interaktionen
                    </div>",
                    $row['total_interactions']
                );
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

    /* Filter-Styling */
    .status-filter .item {
        padding: 0.5em !important;
    }

    /* Filter-Styling */
    .status-filter .item {
        padding: 0.5em !important;
    }

    .status-filter .item .ui.label {
        width: 100%;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .status-filter .item .detail {
        margin-left: auto;
        font-weight: bold;
        opacity: 0.8;
    }

    /* Labels in der Tabelle */
    .ui.small.labels {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }

    .ui.small.labels .label {
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
    }

    .ui.small.labels .label i.icon {
        margin-right: 4px;
    }

    /* Tooltip-Verbesserungen */
    .ui.popup {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Hover-Effekte */
    .ui.label:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>

<script>
    $(document).ready(function () {
        // Tooltips initialisieren
        $('.ui.statistic, .ui.label[data-tooltip]').popup({
            position: 'top center'
        });

        // Filter-Popup Verzögerung
        $('.status-filter .item').popup({
            delay: {
                show: 300,
                hide: 100
            }
        });

        // Wenn der Newsletter noch versendet wird, regelmäßig aktualisieren
        if (<?php echo $newsletter['send_status'] == 1 ? 'true' : 'false' ?>) {
            setInterval(function () {
                if (typeof standardReloadTable === 'function') {
                    ReloadTable();
                }
            }, 30000);
        }

    });
</script>