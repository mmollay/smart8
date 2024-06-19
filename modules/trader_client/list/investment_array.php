<?php
// Configuration for MySQL data fetching

// Definieren Sie die Zeitfenster, die ausgeschlossen werden sollen
$exclusionPeriods = [
    ['start' => "2024-04-15d", 'end' => "2024-04-24"],
    ['start' => "2024-05-01", 'end' => "2024-05-10"],
    // Fügen Sie weitere Zeitfenster nach Bedarf hinzu
];

// Erstellen Sie eine Bedingung, die alle Zeitfenster ausschließt
$exclusionConditions = array_map(function ($period) {
    return '(orders.time < UNIX_TIMESTAMP("' . $period['start'] . '") OR orders.time > UNIX_TIMESTAMP("' . $period['end'] . '"))';
}, $exclusionPeriods);
$exclusionClause = implode(' AND ', $exclusionConditions);

$sqlQuery = array(
    'table' => "ssi_trader.deposits
                LEFT JOIN ssi_trader.clients ON deposits.client_id = clients.client_id
                LEFT JOIN ssi_trader.profit_shares ON deposits.client_id = profit_shares.client_id
                LEFT JOIN ssi_trader.orders ON deposits.account = orders.account",
    'table_total' => "ssi_trader.deposits",
    'field' => "deposits.deposit_id, 
                CASE
                    WHEN SUM(orders.profit) > 0 THEN SUM(orders.profit) * deposits.positive_multiplier
                    ELSE SUM(orders.profit) * deposits.negative_multiplier
                END AS profit,
                CONCAT(clients.first_name, ' ', clients.last_name) AS investor_name, 
                deposits.amount, 
                deposits.deposit_date, 
                deposits.description AS deposit_description, 
                deposits.positive_multiplier, deposits.negative_multiplier,
                profit_shares.profit_percentage AS profit_share_percentage, 
                profit_shares.start_date AS profit_start_date, 
                profit_shares.end_date AS profit_end_date, 
                IF(LENGTH(profit_shares.comment) > 20, CONCAT(SUBSTRING(profit_shares.comment, 1, 20), '...'), profit_shares.comment) AS profit_comment",
    'debug' => true,
    'limit' => 25,
    'group' => 'deposits.deposit_id',
    'where' => 'deposits.client_id = ' . $_SESSION['client_id'] . '
                AND ' . $exclusionClause
);

// Verwenden Sie diese Variable in Ihren Datenbankabfragen
// Beispiel: $result = $mysqli->query(buildQuery($sqlQuery));

//debug
//$arr['mysql']['debug'] = true;


$arr['list'] = ['id' => 'investment', 'width' => '1100px', 'size' => 'large', 'class' => 'unstackable celled striped'];

// Defining table headers
//$arr['th']['deposit_id'] = array('title' => "ID");
$arr['th']['profit_start_date'] = array('title' => "<i class='calendar icon'></i>Date", 'witdh' => '150px');
$arr['th']['amount'] = array('title' => "Deposit", 'format' => 'number', 'align' => 'right', 'total' => true, 'width' => '150px');
$arr['th']['profit'] = array('title' => "Profit", 'format' => 'number', 'align' => 'right', 'width' => '150px');

//$arr['th']['profit_end_date'] = array('title' => "<i class='calendar icon'></i>End Date");
//$arr['th']['profit_comment'] = array('title' => "Comment");

