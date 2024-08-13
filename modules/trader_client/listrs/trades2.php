<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../config.php';

// Konfiguration des ListGenerators
$listConfig = [
    'listId' => 'orders',
    'contentId' => 'content_orders',
    'itemsPerPage' => 50,
    'sortColumn' => $_GET['sort'] ?? 'o.time',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => 'Keine Daten gefunden.',
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '1100px',
    'tableClasses' => 'ui celled striped definition small compact unstackable table'
];

$listGenerator = new ListGenerator($listConfig);

// Zeitfilter erstellen
$currentYear = date('Y');
$array_filter_time_periods = [
    "YEAR(FROM_UNIXTIME(o.time)) = $currentYear" => 'Current Year',
    'DATE(FROM_UNIXTIME(o.time)) = CURDATE()' => 'Today',
    'DATE(FROM_UNIXTIME(o.time)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Yesterday',
    'DATE(FROM_UNIXTIME(o.time)) = DATE_SUB(CURDATE(), INTERVAL 2 DAY)' => 'Day Before Yesterday',
    'YEARWEEK(FROM_UNIXTIME(o.time), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Last Week',
    'MONTH(FROM_UNIXTIME(o.time)) = MONTH(CURDATE()) - 1 AND YEAR(FROM_UNIXTIME(o.time)) = YEAR(CURDATE())' => 'Last Month',
];

// Letzte 6 Monate hinzufügen
for ($i = 1; $i <= 6; $i++) {
    $monthYear = date('F Y', strtotime("-$i month"));
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));
    $condition = "MONTH(FROM_UNIXTIME(o.time)) = $month AND YEAR(FROM_UNIXTIME(o.time)) = $year";
    $array_filter_time_periods[$condition] = $monthYear;
}

// Zeitfilter zum ListGenerator hinzufügen
$listGenerator->addFilter('time_period', 'Zeitraum', $array_filter_time_periods, [
    'placeholder' => '--Zeitraum wählen--',
    'default_value' => "YEAR(FROM_UNIXTIME(o.time)) = $currentYear"
]);

// Gruppierungsoptionen
$groupOptions = [
    'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")' => 'Tage',
    'YEARWEEK(FROM_UNIXTIME(o.time), 1)' => 'Wochen',
    'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m")' => 'Monate',
    'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y")' => 'Jahre',
];

// Gruppierungsfilter zum ListGenerator hinzufügen
$listGenerator->addFilter('group_by', 'Gruppierung', $groupOptions, [
    'placeholder' => '--Gruppierung wählen--',
    'default_value' => 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")'
]);

// Hauptquery
$query = "
    SELECT 
        o.broker_id,
        CASE
            WHEN SUM(o.profit) > 0 THEN SUM(o.profit) * :positiveMultiplier
            ELSE SUM(o.profit) * :negativeMultiplier
        END AS profit,
        DATE_FORMAT(MAX(FROM_UNIXTIME(o.time)), '%Y-%m-%d') AS exit_time,
        FROM_UNIXTIME(o.time) AS readable_time,
        CONCAT ('KW ', WEEK(FROM_UNIXTIME(o.time), 1)) AS kw,
        DAYNAME(FROM_UNIXTIME(o.time)) AS weekday,
        o.account
    FROM 
        ssi_trader.orders AS o
    WHERE 
        o.account = :accountId AND o.trash = '' AND $exclusionClause
    GROUP BY 
        {group_by}
";

$listGenerator->setDatabase($db, $query, true);

// Spalten definieren
$columns = [
    ['name' => 'kw', 'label' => 'Woche', 'width' => '100px'],
    ['name' => 'weekday', 'label' => 'Wochentag', 'width' => '100px'],
    ['name' => 'exit_time', 'label' => 'Tag', 'width' => '100px'],
    [
        'name' => 'profit',
        'label' => 'Profit',
        'formatter' => function ($value) {
            $class = $value > 0 ? 'positive' : 'negative';
            return "<span class='ui {$class} text'>" . number_format($value, 2) . "</span>";
        },
        'allowHtml' => true,
        'sum' => true
    ],
];

// Spalten zum ListGenerator hinzufügen
foreach ($columns as $column) {
    $listGenerator->addColumn($column['name'], $column['label'], $column);
}

// Liste generieren und ausgeben
echo $listGenerator->generateList();

// Datenbankverbindung schließen
if (isset($db)) {
    $db->close();
}