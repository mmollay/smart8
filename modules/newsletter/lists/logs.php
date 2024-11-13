<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../n_config.php';

// Am Anfang der logs.php
$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) :
    (isset($_POST['content_id']) ? intval($_POST['content_id']) : 0);

if ($content_id === 0) {
    echo "<div class='ui negative message'>
            <div class='header'>Fehler</div>
            <p>Keine Newsletter-ID übermittelt</p>
          </div>";
    exit;
}

// Überprüfe zuerst, ob der Newsletter existiert
$stmt = $db->prepare("
    SELECT 
        subject,
        created_at,
        (SELECT COUNT(DISTINCT recipient_id) FROM email_jobs WHERE content_id = email_contents.id) as total_recipients
    FROM email_contents 
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $db->error);
}

$stmt->bind_param("i", $content_id);
$stmt->execute();
$newsletter = $stmt->get_result()->fetch_assoc();

if (!$newsletter) {
    echo "<div class='ui negative message'>
            <div class='header'>Newsletter nicht gefunden</div>
            <p>Der angeforderte Newsletter konnte nicht gefunden werden.</p>
          </div>";
    exit;
}

// Konfiguration des ListGenerators für Logs
$listConfig = [
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
];

$listGenerator = new ListGenerator($listConfig);

// Query für die Log-Daten
$query = "
    SELECT 
        sl.id,
        sl.event as event,  -- Expliziter Alias
        sl.timestamp,
        sl.email,
        COALESCE(r.first_name, '') as first_name,
        COALESCE(r.last_name, '') as last_name,
        COALESCE(ej.error_message, '') as error_message,
        COALESCE(r.company, '') as company,
        COALESCE(r.id, 0) as recipient_id,
        COALESCE(ej.status, '') as current_status,
        COUNT(DISTINCT sl2.id) as total_interactions
    FROM 
        email_jobs ej
        LEFT JOIN status_log sl ON ej.message_id = sl.message_id
        LEFT JOIN status_log sl2 ON sl.message_id = sl2.message_id AND sl2.event IN ('open', 'click')
        LEFT JOIN recipients r ON ej.recipient_id = r.id
    WHERE 
        ej.content_id = {$content_id}
        AND sl.id IS NOT NULL
    GROUP BY 
        sl.id, 
        sl.event,
        sl.timestamp,
        sl.email,
        r.first_name,
        r.last_name,
        ej.error_message,
        r.company,
        r.id,
        ej.status
";

$listGenerator->setSearchableColumns(['sl.email', 'r.first_name', 'r.last_name', 'r.company']);
$listGenerator->setDatabase($db, $query, true);

$listGenerator->addFilter('sl.event', 'Ereignis', $eventTypes);

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
        'name' => 'event',
        'label' => '<i class="event icon"></i>Ereignis',
        'formatter' => function ($value, $row) {
            $icons = [
                'send' => 'paper plane blue',
                'delivered' => 'check circle green',
                'open' => 'eye blue',
                'click' => 'mouse pointer blue',
                'bounce' => 'exclamation circle red',
                'failed' => 'times circle red',
                'blocked' => 'ban red',
                'spam' => 'warning sign orange'
            ];

            $labels = [
                'send' => 'Versendet',
                'delivered' => 'Zugestellt',
                'open' => 'Geöffnet',
                'click' => 'Angeklickt',
                'bounce' => 'Zurückgewiesen',
                'failed' => 'Fehlgeschlagen',
                'blocked' => 'Blockiert',
                'spam' => 'Als Spam markiert'
            ];

            $icon = $icons[$value] ?? 'question';
            $label = $labels[$value] ?? $value;

            return "<i class='{$icon} icon'></i> {$label}";
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
                $html .= "<span class='ui gray large text'>" . htmlspecialchars($row['company']) . "</span>";
            }
            $html .= "</div>";
            return $html ?: '-';
        },
        'allowHtml' => true
    ],
    [
        'name' => 'status_info',
        'label' => '<i class="info circle icon"></i>Status',
        'formatter' => function ($value, $row) {
            $html = "<div class='ui small labels'>";

            // Aktueller Status
            $statusColors = [
                'send' => 'blue',
                'delivered' => 'green',
                'failed' => 'red',
                'bounce' => 'red',
                'blocked' => 'red',
                'spam' => 'orange',
                'open' => 'teal',
                'click' => 'teal'
            ];

            $currentStatus = $row['current_status'] ?: 'unknown';
            $color = $statusColors[$currentStatus] ?? 'grey';
            $html .= "<div class='ui {$color} label'>" . ucfirst($currentStatus) . "</div>";

            // Interaktionen
            if ($row['total_interactions'] > 0) {
                $html .= "<div class='ui teal label'>{$row['total_interactions']} Interaktionen</div>";
            }

            $html .= "</div>";

            // Fehlermeldung falls vorhanden
            if (!empty($row['error_message'])) {
                $html .= "<div class='ui negative tiny message'>" . htmlspecialchars($row['error_message']) . "</div>";
            }

            return $html;
        },
        'allowHtml' => true
    ]
];

// Spalten zum ListGenerator hinzufügen
foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Header mit Newsletter-Informationen
echo "
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
</div>";

// Liste generieren
echo $listGenerator->generateList();

// Schließen der Datenbankverbindung
if (isset($db)) {
    $db->close();
}
?>

<script>
    $(document).ready(function () {
        // Initialisiere Semantic UI Komponenten
        $('.ui.popup').popup();

        // Aktualisiere die Liste automatisch alle 30 Sekunden wenn der Newsletter noch versendet wird
        function checkSendStatus() {
            $.ajax({
                url: 'ajax/check_newsletter_status.php',
                data: { content_id: <?php echo $content_id; ?> },
                success: function (response) {
                    if (response.is_sending) {
                        setTimeout(function () {
                            if (typeof reloadTable === 'function') {
                                reloadTable();
                            }
                            checkSendStatus();
                        }, 30000);
                    }
                }
            });
        }

        checkSendStatus();
    });
</script>