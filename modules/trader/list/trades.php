<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'orders',
    'contentId' => 'content_orders',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'invoice_number',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine Daten gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped  small compact very selectable table',
    'debug' => true,
    'allowHtml' => true,
]);

// Datenbank-Verbindung (angenommen, dass $connection bereits existiert)
$db = $connection;

// SQL-Abfrage
$query = "
    SELECT
        o.ticket, o.order_id, o.time, o.time_msc, o.type, o.magic, o.position_id, o.reason, lotgroup_id, o.server_id, o.account, b.title AS broker_name,
        CONCAT(c.first_name, ' ', c.last_name) AS client_name,
        CASE 
            WHEN COUNT(*) = 1 THEN ROUND(o.volume, 2)
            WHEN COUNT(*) = 2 THEN ROUND(o.volume, 2)
            ELSE ROUND(SUM(o.volume) / 2, 2)
        END AS volume,
        MAX(o.entry) entry,
        CEIL(COUNT(*) / 2) AS level,
        MIN(o.price) AS min_price, 
        MAX(o.price) AS max_price, 
        o.commission, o.swap, SUM(o.profit) profit, o.fee, o.symbol_id, 
        MIN(FROM_UNIXTIME(o.time)) AS entry_time, 
        MAX(FROM_UNIXTIME(o.time)) AS exit_time, 
        FROM_UNIXTIME(o.time) AS readable_time, 
        s.symbol, trash, o.strategy
    FROM
        ssi_trader.orders AS o 
        LEFT JOIN ssi_trader.symbols AS s ON o.symbol_id = s.symbol_id
        LEFT JOIN ssi_trader.broker AS b ON o.account = b.user
        LEFT JOIN ssi_trader.clients AS c ON o.account = c.account
    WHERE
        trash = '' AND $exclusionClause
    GROUP BY
        o.lotgroup_id
";

$listGenerator->setSearchableColumns(['lotgroup_id', 'position_id', 'o.account']);
$listGenerator->setDatabase($db, $query, true);

// Zeitraum-Filter
$array_filter_time_periods = [
    'YEAR(FROM_UNIXTIME(time)) = ' . date('Y') => 'Aktuelles Jahr',
    'DATE(FROM_UNIXTIME(time)) = CURDATE()' => 'Heute',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Gestern',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 2 DAY)' => 'Vorgestern',
    'YEARWEEK(FROM_UNIXTIME(time), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Letzte Woche',
    'MONTH(FROM_UNIXTIME(time)) = MONTH(CURDATE()) - 1 AND YEAR(FROM_UNIXTIME(time)) = YEAR(CURDATE())' => 'Letzter Monat',
];

// Hinzufügen der letzten sechs Monate
for ($i = 1; $i <= 6; $i++) {
    $monthYear = date('F Y', strtotime("-$i month"));
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));
    $condition = "MONTH(FROM_UNIXTIME(time)) = $month AND YEAR(FROM_UNIXTIME(time)) = $year";
    $array_filter_time_periods[$condition] = $monthYear;
}

$listGenerator->addFilter('select_day', 'Zeitraum', $array_filter_time_periods, [
    'type' => 'dropdown',
    'placeholder' => '--Zeitraum auswählen--',
    'default_value' => 'YEAR(FROM_UNIXTIME(time)) = ' . date('Y'),
    'filterType' => 'complex'
]);

// Broker/Client Filter
$array_filter_broker = getBrokerClientList($connection);
$listGenerator->addFilter('o.account', 'Clients/Brokers', $array_filter_broker, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Clients/Brokers',
]);

// Spalten definieren
$listGenerator->addColumn('order_id', 'Order ID');
$listGenerator->addColumn('min_price', 'Min Preis', ['formatter' => 'number']);
$listGenerator->addColumn('max_price', 'Max Preis', ['formatter' => 'number']);
$listGenerator->addColumn('type', 'Typ', [
    'replace' => [
        'default' => '',
        '1' => "<span class='ui blue text'>Buy</span>",
        '0' => "<span class='ui red text'>Sell</span>"
    ],
    'align' => 'center',
    'width' => '70px',
    'allowHtml' => true  // Wichtig, um HTML in der Ersetzung zu erlauben
]);

$listGenerator->addColumn('entry', 'Einstieg', ['formatter' => 'number', 'align' => 'right', 'width' => '50px']);
$listGenerator->addColumn('volume', 'Volumen');
$listGenerator->addColumn('account', 'Konto');
$listGenerator->addColumn('broker_name', 'Broker', ['nowrap' => true]);
$listGenerator->addColumn('strategy', 'Strategie');
$listGenerator->addColumn('level', 'Hedge Anzahl');
$listGenerator->addColumn('time', 'Lesbare Zeit');
$listGenerator->addColumn('exit_time', 'Ausstiegszeit', ['width' => '100px', 'nowrap' => true]);
$listGenerator->addColumn('profit', 'Gewinn', [
    'formatter' => 'euro',
    'align' => 'right',
    'showTotal' => true,
    'totalType' => 'sum',
    'totalLabel' => '',
]);

// Buttons definieren
// $listGenerator->addButton('details', [
//     'icon' => 'list',
//     'class' => 'blue tiny',
//     'position' => 'left',
//     'popup' => 'Details',
//     'modalId' => 'modal_form'
// ]);

$listGenerator->addButton('delete', [
    'icon' => 'trash',
    'class' => 'tiny',
    'position' => 'right',
    'popup' => 'Löschen',
    'modalId' => 'modal_form_delete'
]);

// Modals definieren
$listGenerator->addModal('modal_form', [
    'title' => 'Bearbeiten',
    'url' => 'form_edit.php'
]);

$listGenerator->addModal('modal_form_delete', [
    'title' => 'Entfernen',
    'class' => 'small',
    'url' => 'form_delete.php'
]);

echo $listGenerator->generateList();

// Hilfsfunktionen (getBrokerClientList, etc.) hier einfügen...
?>