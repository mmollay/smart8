<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'trades',
    'contentId' => 'content_trades',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'bitget_timestamp',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine Trades gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped small compact very selectable table',
    'debug' => true,
    'allowHtml' => true,
]);

// Basis Query für Trades und PnL
$query = "
    SELECT 
        t.*,
        u.username as user_name,
        FROM_UNIXTIME(t.bitget_timestamp/1000) as trade_time,
        t.size * t.price as volume,
        CASE 
            WHEN t.side = 'open_short' THEN 'Open Short'
            WHEN t.side = 'open_long' THEN 'Open Long'
            WHEN t.side = 'close_long' THEN 'Close Long'
            WHEN t.side = 'close_short' THEN 'Close Short'
            ELSE 'Unknown'
        END as trade_side
    FROM trades t
    LEFT JOIN users u ON t.user_id = u.id
    GROUP BY t.id
";

// User-Filter hinzufügen
$userQuery = "SELECT id, username as name FROM users ORDER BY username";
$userResult = $db->query($userQuery);
$userOptions = [];
while ($row = $userResult->fetch_assoc()) {
    $userOptions[$row['id']] = $row['name'];
}

// Symbol-Filter
$symbolQuery = "SELECT DISTINCT symbol FROM trades ORDER BY symbol";
$symbolResult = $db->query($symbolQuery);
$symbolOptions = [];
while ($row = $symbolResult->fetch_assoc()) {
    $symbolOptions[$row['symbol']] = $row['symbol'];
}

// Suchbare Spalten
$listGenerator->setSearchableColumns([
    'symbol',
    'order_id',
    'user_name',
    'trade_time'
]);

$listGenerator->setDatabase($db, $query, true);

// Zeitraum-Filter
$array_filter_time_periods = [
    'DATE(FROM_UNIXTIME(t.bitget_timestamp/1000)) = CURDATE()' => 'Heute',
    'DATE(FROM_UNIXTIME(t.bitget_timestamp/1000)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Gestern',
    'YEARWEEK(FROM_UNIXTIME(t.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1)' => 'Diese Woche',
    'YEARWEEK(FROM_UNIXTIME(t.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Letzte Woche',
    'MONTH(FROM_UNIXTIME(t.bitget_timestamp/1000)) = MONTH(CURDATE())' => 'Dieser Monat',
    'MONTH(FROM_UNIXTIME(t.bitget_timestamp/1000)) = MONTH(CURDATE()) - 1' => 'Letzter Monat',
];

// Filter hinzufügen
$listGenerator->addFilter('t.user_id', 'Benutzer', $userOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Benutzer',
    'searchable' => true
]);

$listGenerator->addFilter('t.symbol', 'Symbol', $symbolOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Symbole'
]);

$listGenerator->addFilter('time_period', 'Zeitraum', $array_filter_time_periods, [
    'type' => 'dropdown',
    'placeholder' => '--Zeitraum auswählen--',
    'filterType' => 'complex'
]);

// Spalten definieren
$listGenerator->addColumn('trade_time', 'Zeit', [
    'formatter' => 'datetime',
    'width' => '150px'
]);

$listGenerator->addColumn('user_name', 'Benutzer', [
    'width' => '120px',
    'nowrap' => true
]);

$listGenerator->addColumn('symbol', 'Symbol', [
    'width' => '100px'
]);

$listGenerator->addColumn('trade_side', 'Seite', [
    'formatter' => function ($value) {
        $sideMap = [
            'Open Long' => ['color' => 'green', 'icon' => 'arrow alternate circle up', 'text' => 'LONG'],
            'Open Short' => ['color' => 'red', 'icon' => 'arrow alternate circle down', 'text' => 'SHORT'],
            'Close Long' => ['color' => 'red', 'icon' => 'arrow alternate circle down', 'text' => 'LONG'],
            'Close Short' => ['color' => 'green', 'icon' => 'arrow alternate circle up', 'text' => 'SHORT'],
            'Unknown' => ['color' => 'grey', 'icon' => 'question circle', 'text' => 'UNKNOWN']
        ];

        $sideInfo = $sideMap[$value] ?? $sideMap['Unknown'];
        return sprintf(
            '<span class="ui %s text"><i class="icon %s"></i> %s</span>',
            $sideInfo['color'],
            $sideInfo['icon'],
            $sideInfo['text']
        );
    },
    'align' => 'center',
    'width' => '120px',
    'allowHtml' => true
]);

$listGenerator->addColumn('size', 'Menge', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px'
]);

$listGenerator->addColumn('price', 'Preis', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px'
]);

$listGenerator->addColumn('volume', 'Volumen USDT', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '120px',
    'showTotal' => true,
]);

$listGenerator->addColumn('profit', 'Real. PnL', [
    'formatter' => 'number_color',
    'align' => 'right',
    'width' => '100px',
    'showTotal' => true,
]);

$listGenerator->addColumn('fee', 'Gebühr', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px',
    'showTotal' => true,
]);

// $listGenerator->addColumn('fee_coin', 'Fee Coin', [
//     'width' => '80px',
//     'align' => 'right',
// ]);

$listGenerator->addColumn('order_id', 'Order ID', [
    'width' => '200px',
    'nowrap' => true
]);

// Export-Konfiguration
$listGenerator->addExport([
    'format' => 'csv',
    'title' => 'Export',
    'popup' => ['content' => 'Als CSV exportieren']
]);

// Liste generieren
echo $listGenerator->generateList();
?>