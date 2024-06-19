<?php
include_once (__DIR__ . '/../config.php');
include_once (__DIR__ . '/../functions.php');

$clientId = $_SESSION['client_id'];
$array = getBrokerUserByClientId($db, $clientId);
$accountId = $array['account'];
$positiveMultiplier = $array['positive_multiplier'];
$negativeMultiplier = $array['negative_multiplier'];

// Initialize the array with the "Current Year" filter
$array_filter_time_periods = [
    'YEAR(FROM_UNIXTIME(time)) = ' . date('Y') => 'Current Year',
];

// Existing filters for specific time periods
$array_filter_time_periods += [
    'DATE(FROM_UNIXTIME(time)) = CURDATE()' => 'Today',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)' => 'Yesterday',
    'DATE(FROM_UNIXTIME(time)) = DATE_SUB(CURDATE(), INTERVAL 2 DAY)' => 'Day Before Yesterday',
    'YEARWEEK(FROM_UNIXTIME(time), 1) = YEARWEEK(CURDATE(), 1) - 1' => 'Last Week',
    'MONTH(FROM_UNIXTIME(time)) = MONTH(CURDATE()) - 1 AND YEAR(FROM_UNIXTIME(time)) = YEAR(CURDATE())' => 'Last Month',
];

// Add filters for the last six months, including the year
for ($i = 1; $i <= 6; $i++) {
    $monthYear = date('F Y', strtotime("-$i month"));
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));
    // Generate the SQL condition
    $condition = "MONTH(FROM_UNIXTIME(time)) = $month AND YEAR(FROM_UNIXTIME(time)) = $year";
    // Add month and year to the display text
    $array_filter_time_periods[$condition] = $monthYear;
}


$arr['mysql'] = [
    'table' => 'ssi_trader.orders AS o',
    'field' => "broker_id,       
                CASE
                    WHEN SUM(o.profit) > 0 THEN SUM(o.profit) * $positiveMultiplier
                    ELSE SUM(o.profit) * $negativeMultiplier
                END AS profit,
                DATE_FORMAT(MAX(FROM_UNIXTIME(o.time)), '%Y-%m-%d') AS exit_time,
                FROM_UNIXTIME(o.time) AS readable_time, 
                CONCAT ('KW ', WEEK(FROM_UNIXTIME(o.time), 1)) AS kw,
                DAYNAME(FROM_UNIXTIME(o.time)) AS weekday,
                account",
    'order' => 'o.time DESC',
    'limit' => 50,
    'debug' => 0,
    'group' => 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")',
    'where' => "AND account = '$accountId' AND trash = '' AND $exclusionClause",
];

//$arr['filter']['select_day'] = array('type' => 'dropdown', 'query' => "{value}", 'array' => $array_filter_time_periods, 'placeholder' => '--Timer period--', 'default_value' => 'YEAR(FROM_UNIXTIME(time)) = ' . date('Y'));

//broker_id
//$arr['order'] = array('default' => 'time desc', 'array' => array('order_id desc' => 'Order ID', 'position_id desc, order_id desc' => 'Position', 'lotgroup_id desc, position_id desc, order_id desc' => 'Lot Group desc', 'time desc' => 'Time desc', 'profit desc' => 'Profit desc', 'level desc' => 'Hegde Count'));

//group
$arr['group'] = array(
    'default' => 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")',
    'array' => array(
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")' => 'Days',
        'YEARWEEK(FROM_UNIXTIME(o.time), 1)' => 'Weeks',
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m")' => 'Months',
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y")' => 'Years',
    ),
    'default_value' => 'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")'
);

$arr['list'] = ['id' => 'orders', 'width' => '1100px', 'size' => 'large', 'class' => ' unstackable celled striped'];

$arr['th']['kw'] = ['title' => "Week", 'width' => '100px'];
$arr['th']['weekday'] = ['title' => "Weekday", 'width' => '100px'];
$arr['th']['exit_time'] = ['title' => "Day", 'width' => '100px'];
$arr['th']['profit'] = [
    'title' => "Profit",
    'format' => 'number_redblue',
    'align' => 'right',
    'total' => "
    SELECT SUM(profit) AS sum_profit FROM ( 
        SELECT CASE WHEN SUM(o.profit) > 0 THEN SUM(o.profit) * 0.50 ELSE SUM(o.profit) * 0.10 END AS profit FROM ssi_trader.orders AS o WHERE 1
    {where} ) AS sub WHERE 1"
];