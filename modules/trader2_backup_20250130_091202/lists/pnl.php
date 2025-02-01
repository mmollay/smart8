<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

// Füge net_profit Spalte hinzu, falls sie noch nicht existiert
$checkColumn = $db->query("SHOW COLUMNS FROM pnl_history LIKE 'net_profit'");
if ($checkColumn->num_rows === 0) {
    $db->query("ALTER TABLE pnl_history ADD COLUMN net_profit DECIMAL(20,8) DEFAULT 0 AFTER profit");
}

$listGenerator = new ListGenerator([
    'listId' => 'pnl',
    'contentId' => 'content_pnl',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'bitget_timestamp',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine PnL Einträge gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped small compact very selectable table',
    'debug' => true,
    'allowHtml' => true,
]);

// Basis Query für PnL History
$query = "
    SELECT 
        u.username as user_name,
        DATE_FORMAT(FROM_UNIXTIME(p.bitget_timestamp/1000), '%Y-%m-%d %H:%i:%s') as trade_time,
        p.symbol,
        p.side,
        p.size,
        p.entry_price,
        p.exit_price,
        p.size * p.entry_price as entry_volume,
        p.size * p.exit_price as exit_volume,
        p.profit as raw_pnl,
        p.net_profit,
        p.leverage,
        p.bitget_timestamp,
        CASE 
            WHEN p.side = 'long' THEN 'Long'
            WHEN p.side = 'short' THEN 'Short'
            ELSE p.side
        END as position_type
    FROM pnl_history p
    LEFT JOIN users u ON p.user_id = u.id
    WHERE 1=1
";

// User-Filter hinzufügen
$userQuery = "SELECT id, username as name FROM users ORDER BY username";
$userResult = $db->query($userQuery);
$userOptions = [];
while ($row = $userResult->fetch_assoc()) {
    $userOptions[$row['id']] = $row['name'];
}

// Symbol-Filter
$symbolQuery = "SELECT DISTINCT symbol FROM pnl_history ORDER BY symbol";
$symbolResult = $db->query($symbolQuery);
$symbolOptions = [];
while ($row = $symbolResult->fetch_assoc()) {
    $symbolOptions[$row['symbol']] = $row['symbol'];
}

// Suchbare Spalten
$listGenerator->setSearchableColumns([
    'symbol',
    'user_name',
    'trade_time'
]);

$listGenerator->setDatabase($db, $query, true);

// Zeitraum-Filter
$array_filter_time_periods = [
    'DATE(FROM_UNIXTIME(p.bitget_timestamp/1000)) = CURDATE()' => 'Heute',
    'DATE(FROM_UNIXTIME(p.bitget_timestamp/1000)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Gestern',
    'YEARWEEK(FROM_UNIXTIME(p.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1)' => 'Diese Woche',
    'YEARWEEK(FROM_UNIXTIME(p.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Letzte Woche',
    'MONTH(FROM_UNIXTIME(p.bitget_timestamp/1000)) = MONTH(CURDATE())' => 'Dieser Monat',
    'MONTH(FROM_UNIXTIME(p.bitget_timestamp/1000)) = MONTH(CURDATE()) - 1' => 'Letzter Monat',
];

// Filter hinzufügen
$listGenerator->addFilter('p.user_id', 'Benutzer', $userOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Benutzer',
    'searchable' => true
]);

$listGenerator->addFilter('p.symbol', 'Symbol', $symbolOptions, [
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

$listGenerator->addColumn('position_type', 'Position', [
    'replace' => [
        'Long' => "<span class='ui blue text'>Long</span>",
        'Short' => "<span class='ui red text'>Short</span>"
    ],
    'align' => 'center',
    'width' => '80px',
    'allowHtml' => true
]);

$listGenerator->addColumn('size', 'Menge', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px'
]);

$listGenerator->addColumn('entry_price', 'Einstieg', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px'
]);

$listGenerator->addColumn('exit_price', 'Ausstieg', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '100px'
]);

$listGenerator->addColumn('entry_volume', 'Einstieg USDT', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '120px',
    'showTotal' => true,
]);

$listGenerator->addColumn('exit_volume', 'Ausstieg USDT', [
    'formatter' => 'number',
    'align' => 'right',
    'width' => '120px',
    'showTotal' => true,
]);

$listGenerator->addColumn('raw_pnl', 'PnL', [
    'align' => 'right',
    'width' => '100px',
    'formatter' => 'number',
    'showTotal' => true,
]);

$listGenerator->addColumn('net_profit', 'Net PnL', [
    'align' => 'right',
    'width' => '100px',
    'formatter' => 'number',
    'showTotal' => true,
]);

$listGenerator->addColumn('leverage', 'Hebel', [
    'align' => 'center',
    'width' => '80px'
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