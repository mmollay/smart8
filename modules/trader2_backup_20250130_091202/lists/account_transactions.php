<?php
include __DIR__ . '/../../../smartform2/ListGenerator.php';
include __DIR__ . '/../t_config.php';

$listGenerator = new ListGenerator([
    'listId' => 'account_transactions',
    'contentId' => 'content_account_transactions',
    'itemsPerPage' => 25,
    'sortColumn' => $_GET['sort'] ?? 'bitget_timestamp',
    'sortDirection' => strtoupper($_GET['sortDir'] ?? 'DESC'),
    'page' => intval($_GET['page'] ?? 1),
    'search' => $_GET['search'] ?? '',
    'showNoDataMessage' => true,
    'noDataMessage' => "Keine Transaktionen gefunden.",
    'striped' => true,
    'selectable' => true,
    'celled' => true,
    'width' => '100%',
    'tableClasses' => 'ui celled striped small compact very selectable table',
    'debug' => true,
    'allowHtml' => true,
]);

// Basis Query für Account Transaktionen
$baseQuery = "
    SELECT 
        at.*,
        u.username as user_name,
        FROM_UNIXTIME(at.bitget_timestamp/1000) as transaction_time
    FROM account_transactions at
    LEFT JOIN users u ON at.user_id = u.id
    GROUP BY at.id
";

// User-Filter hinzufügen
$userQuery = "SELECT id, username as name FROM users ORDER BY username";
$userResult = $db->query($userQuery);
$userOptions = [];
while ($row = $userResult->fetch_assoc()) {
    $userOptions[$row['id']] = $row['name'];
}

// Transaktionstyp-Filter
$typeOptions = [
    'deposit' => 'Einzahlung',
    'withdrawal' => 'Auszahlung'
];

// Währungs-Filter
$currencyQuery = "SELECT DISTINCT currency FROM account_transactions ORDER BY currency";
$currencyResult = $db->query($currencyQuery);
$currencyOptions = [];
while ($row = $currencyResult->fetch_assoc()) {
    $currencyOptions[$row['currency']] = $row['currency'];
}

// Suchbare Spalten
$listGenerator->setSearchableColumns([
    'transaction_id',
    'user_name',
    'transaction_time',
    'type',
    'currency'
]);

// Zeitraum-Filter
$array_filter_time_periods = [
    'DATE(FROM_UNIXTIME(at.bitget_timestamp/1000)) = CURDATE()' => 'Heute',
    'DATE(FROM_UNIXTIME(at.bitget_timestamp/1000)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Gestern',
    'YEARWEEK(FROM_UNIXTIME(at.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1)' => 'Diese Woche',
    'YEARWEEK(FROM_UNIXTIME(at.bitget_timestamp/1000), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Letzte Woche',
    'MONTH(FROM_UNIXTIME(at.bitget_timestamp/1000)) = MONTH(CURDATE())' => 'Dieser Monat',
    'MONTH(FROM_UNIXTIME(at.bitget_timestamp/1000)) = MONTH(CURDATE()) - 1' => 'Letzter Monat',
];

// Filter hinzufügen
$listGenerator->addFilter('at.user_id', 'Benutzer', $userOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Benutzer',
    'searchable' => true
]);

$listGenerator->addFilter('at.type', 'Typ', $typeOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Typen'
]);

$listGenerator->addFilter('at.currency', 'Währung', $currencyOptions, [
    'type' => 'dropdown',
    'placeholder' => 'Alle Währungen'
]);

$listGenerator->addFilter('time_period', 'Zeitraum', $array_filter_time_periods, [
    'type' => 'dropdown',
    'placeholder' => '--Zeitraum auswählen--',
    'filterType' => 'complex'
]);

$listGenerator->setDatabase($db, $baseQuery, true);

// Spalten definieren
$listGenerator->addColumn('transaction_time', 'Zeit', [
    'formatter' => 'datetime',
    'width' => '150px',
    'sortable' => true
]);

$listGenerator->addColumn('user_name', 'Benutzer', [
    'width' => '120px',
    'sortable' => true
]);

$listGenerator->addColumn('type', 'Typ', [
    'width' => '100px',
    'sortable' => true,
    'formatter' => function ($value) {
        return $value == 'deposit' ? 'Einzahlung' : 'Auszahlung';
    }
]);

$listGenerator->addColumn('amount', 'Betrag', [
    'width' => '120px',
    'sortable' => true,
    'align' => 'right',
    'formatter' => 'number',
    'showTotal' => true
]);

$listGenerator->addColumn('currency', 'Währung', [
    'width' => '80px',
    'sortable' => true
]);

$listGenerator->addColumn('status', 'Status', [
    'width' => '100px',
    'sortable' => true,
    'formatter' => function ($value) {
        return $value == 'completed' ? 'Abgeschlossen' : $value;
    }
]);

$listGenerator->addColumn('transaction_id', 'Transaktions-ID', [
    'width' => '200px',
    'sortable' => true
]);

// Liste generieren
echo $listGenerator->generateList();
