<?php
$arr['mysql'] = [
    'table' => 'ssi_trader.orders AS o LEFT JOIN ssi_trader.symbols AS s ON o.symbol_id = s.symbol_id', // Alias 'o' für die Tabelle orders
    'field' => "o.ticket, o.order_id, o.time, o.time_msc, o.type type,  o.magic, o.position_id, o.reason, lotgroup_id, server_id, broker_id,       
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
                s.symbol, trash", // Hinzufügen von s.symbol_name zur Feldliste
    'order' => 'o.time DESC', // Sortierung nach 'time' in orders
    'like' => 'lotgroup_id,position_id',
    'limit' => 100,
    'group' => 'o.lotgroup_id', // Gruppierung nach 'ticket' in orders
    //'group' => 'o.order_id', // Gruppierung nach 'order_id' in orders

    //'group' => "DATE_FORMAT(FROM_UNIXTIME(o.time), '%Y-%m')"
    // 'debug' => true,
    'where' => "AND trash = '' " // Hinzufügen einer Bedingung für 'entry' in orders
];

//group
$arr['group'] = array(
    'default' => 'order_id',
    'array' => array(
        'order_id' => 'Group by oder_id',
        'position_id' => 'Position',
        'lotgroup_id' => 'Hedges',
        'profit' => 'Profit',
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m-%d")' => 'Days',
        'YEARWEEK(FROM_UNIXTIME(o.time), 1)' => 'Weeks',
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y-%m")' => 'Months',
        'DATE_FORMAT(FROM_UNIXTIME(o.time),"%Y")' => 'Years',
    ),
    'default_value' => 'lotgroup_id'
);

$arr['list'] = [
    'id' => 'orders',
    'width' => '1100px',
    'size' => 'small',
    'class' => 'compact celled striped'
];

// Hinzufügen von Spaltenüberschriften basierend auf der Tabelle `orders`
$arr['th']['order_id'] = ['title' => "Order ID", 'width' => '100px'];
$arr['th']['position_id'] = ['title' => "Position ID", 'width' => '100px'];
//$arr['th']['lotgroup_id'] = ['title' => "Lotgroup ID", 'width' => '100px'];
//$arr['th']['symbol'] = ['title' => "Symbol ID", 'width' => '100px'];
//$arr['th']['server_id'] = ['title' => "Server_ID", 'width' => '100px'];
$arr['th']['min_price'] = ['title' => "Min Price", 'format' => 'number_color', 'width' => '120px'];
$arr['th']['max_price'] = ['title' => "Max Price", 'format' => 'number_color', 'width' => '120px'];
$arr['th']['type'] = ['title' => "Type", 'replace' => array('default' => '', '1' => "<span class='ui blue text'>buy</span>", '0' => "<span class='ui red text'>sell</span>"), 'align' => 'center', 'width' => '50px'];
$arr['th']['entry'] = ['title' => "Entry", 'format' => 'number', 'align' => 'right', 'width' => '50px'];
$arr['th']['volume'] = ['title' => "Volume"];
$arr['th']['level'] = ['title' => "Hedge Count"];
$arr['th']['entry_time'] = ['title' => "Entry Time"];
$arr['th']['exit_time'] = ['title' => "Exit Time"];
$arr['th']['profit'] = ['title' => "Profit", 'format' => 'number_redblue', 'align' => 'right', 'total' => true, 'width' => '120px'];
$arr['th']['trash'] = ['title' => "Trash"];
