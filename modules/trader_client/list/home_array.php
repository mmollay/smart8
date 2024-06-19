<?
$arr['mysql'] = array(
    'table' => 'ssi_trader.clients AS c
            LEFT JOIN ssi_trader.deposits AS d ON c.client_id = d.client_id
            LEFT JOIN ssi_trader.orders AS o ON c.client_id = o.client_id',
    'field' => "c.client_id, CONCAT(c.first_name, ' ', c.last_name) AS client_name,
        SUM(d.amount) AS total_deposits,
        SUM(o.profit) AS total_profit,
        CASE
            WHEN SUM(o.profit) > 0 THEN FORMAT(SUM(o.profit) * c.positive_multiplier, 2)
            ELSE FORMAT(SUM(o.profit) * c.negative_multiplier, 2)
        END AS adjusted_profit",
    'group' => 'c.client_id',
    'like' => 'user'
);

$arr['list'] = array(
    'id' => 'financial_overview',
    'width' => '100%',
    'size' => 'small',
    'class' => 'ui very compact table'
);

$arr['th']['client_id'] = array('title' => "Client ID");
$arr['th']['client_name'] = array('title' => "Client Name");
$arr['th']['total_deposits'] = array('title' => "Total Deposits (€)", 'format' => 'number', 'align' => 'right');
$arr['th']['total_profit'] = array('title' => "Total Profit (€)", 'format' => 'number', 'align' => 'right');
$arr['th']['adjusted_profit'] = array('title' => "Adjusted Profit (€)", 'format' => 'number', 'align' => 'right');

// Debugging könnte hilfreich sein, um die SQL-Abfrage zu überprüfen
$arr['mysql']['debug'] = true;
