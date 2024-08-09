<?php
// Configuration for MySQL data fetching
$arr['mysql'] = array(
    'table' => "ssi_trader.deposits AS d
                LEFT JOIN ssi_trader.clients ON d.client_id = ssi_trader.clients.client_id
                LEFT JOIN ssi_trader.profit_shares ON d.client_id = ssi_trader.profit_shares.client_id
                LEFT JOIN ssi_trader.orders ON d.account = ssi_trader.orders.account AND ssi_trader.orders.trash = 0",
    'table_total' => "ssi_trader.deposits",
    'field' => "d.deposit_id, 
                CONCAT(ssi_trader.clients.first_name, ' ', ssi_trader.clients.last_name) AS investor_name, 
                d.amount, 
                d.deposit_date, 
                d.account,
                CONCAT(FORMAT(d.positive_multiplier * 100, 0), '%') AS positive_multiplier,
                CONCAT(FORMAT(d.negative_multiplier * 100, 0), '%') AS negative_multiplier,
                CASE
                    WHEN SUM(ssi_trader.orders.profit) > 0 THEN SUM(ssi_trader.orders.profit) * d.positive_multiplier
                    ELSE SUM(ssi_trader.orders.profit) * d.negative_multiplier
                END AS profit_client,
                d.description AS deposit_description,
                ssi_trader.profit_shares.profit_percentage AS profit_share_percentage, 
                ssi_trader.profit_shares.start_date AS profit_start_date, 
                ssi_trader.profit_shares.end_date AS profit_end_date, 
                IF(LENGTH(ssi_trader.profit_shares.comment) > 20, CONCAT(SUBSTRING(ssi_trader.profit_shares.comment, 1, 20), '...'), ssi_trader.profit_shares.comment) AS profit_comment",
    'limit' => 25,
    'group' => 'd.deposit_id, ssi_trader.clients.client_id, d.account',
    'like' => 'investor_name'
);


//debug
//$arr['mysql']['debug'] = true;

$arr['list'] = array('id' => 'investment', 'width' => '1200px', 'size' => 'small', 'class' => 'compact celled striped definition', 'class_total' => '');

// Defining table headers
$arr['th']['deposit_id'] = array('title' => "ID");
$arr['th']['investor_name'] = array('title' => "Investor Name");
$arr['th']['amount'] = array('title' => "Deposit Amount ($)", 'format' => 'number', 'align' => 'right', 'total' => true);
$arr['th']['deposit_date'] = array('title' => "<i class='calendar icon'></i>Deposit Date", 'align' => 'center');
$arr['th']['account'] = array('title' => "Broker Account");

//$arr['th']['description'] = array('title' => "Description");
$arr['th']['positive_multiplier'] = array('title' => "Positive Multiplier", 'width' => '50px');
$arr['th']['negative_multiplier'] = array('title' => "Negative Multiplier", 'width' => '50px');
$arr['th']['profit_start_date'] = array('title' => "<i class='calendar icon'></i>Start Date");
$arr['th']['profit_end_date'] = array('title' => "<i class='calendar icon'></i>End Date");
//$arr['th']['profit_comment'] = array('title' => "Comment");
//$arr['th']['profit'] = array('title' => "Total Profit", 'format' => 'number_redblue', 'align' => 'right', 'total' => true, 'width' => '80px');
$arr['th']['profit_client'] = array('title' => "Client Profit", 'format' => 'number_redblue', 'align' => 'right', 'width' => '80px');
$arr['th']['profit_difference'] = array('title' => "Profit Difference", 'format' => 'number_redblue', 'align' => 'right', 'width' => '80px');


// Buttons for creating new records and editing or deleting existing ones
$arr['top']['button']['modal_form'] = array('title' => 'Add Investment', 'icon' => 'plus', 'class' => 'green circular');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue', 'popup' => 'Edit Investment');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash alternate', 'popup' => 'Delete Investment', 'class' => 'red');

// Modal configurations for editing and deleting
$arr['modal']['modal_form'] = array('title' => 'Edit Investment', 'class' => 'resizable scrolling content', 'url' => 'form_edit.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Save', 'color' => 'blue', 'form_id' => 'form_edit');
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Cancel', 'color' => 'grey', 'icon' => 'cancel');

$arr['modal']['modal_form_delete'] = array('title' => 'Confirm Deletion', 'class' => 'small', 'url' => 'form_delete.php');

