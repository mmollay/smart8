<?php


$arr['mysql'] = array(
    'table' => 'ssi_trader.broker
                LEFT JOIN ssi_trader.clients ON broker.user = clients.account',
    'field' => "broker.broker_id, broker.broker_server, broker.user, broker.password, broker.timestamp, broker.title, broker.real_account,
                GROUP_CONCAT(DISTINCT CONCAT(clients.first_name,' ',clients.last_name) ORDER BY clients.first_name ASC SEPARATOR ', ') AS client_names,
                (SELECT SUM(profit) FROM ssi_trader.orders WHERE account = broker.user AND DATE(FROM_UNIXTIME(time)) = CURDATE()) AS daily_profit,
                (SELECT SUM(profit) FROM ssi_trader.orders WHERE account = broker.user AND DATE(FROM_UNIXTIME(time)) = CURDATE() - INTERVAL 1 DAY) AS previous_day_profit",
    'limit' => 25,
    'group' => 'broker.broker_id',
    'like' => 'broker.user'
);
//$arr['mysql']['debug'] = true;

$arr['list'] = array('id' => 'broker', 'width' => '1200px', 'size' => 'small', 'class' => 'compact celled striped definition');

//$arr['th']['broker_id'] = array('title' => "ID");
$arr['th']['user'] = array('title' => "Account", 'align' => 'left');
$arr['th']['real_account'] = array('title' => "Real", 'align' => 'center');
$arr['th']['title'] = array('title' => "Matchcode");
$arr['th']['broker_server'] = array('title' => "Server");
$arr['th']['password'] = array('title' => "Password");
//$arr['th']['timestamp'] = array('title' => "<i class='clock icon'></i>Timestamp", 'align' => 'center');
$arr['th']['client_names'] = array('title' => "Associated Clients", 'align' => 'left'); // Hier zeigst du die verknüpften Client-Namen an
$arr['th']['daily_profit'] = array('title' => "Profit (Today)", 'format' => 'number_color', 'align' => 'right');
$arr['th']['previous_day_profit'] = array('title' => "Profit (Yesterday)", 'format' => 'number_color', 'align' => 'right');

$arr['top']['button']['modal_form'] = array('title' => 'Create', 'icon' => 'plus', 'class' => 'blue circular');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue', 'popup' => 'Edit');
$arr['tr']['button']['left']['modal_form_clone'] = array('icon' => 'copy', 'popup' => 'Klonen');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'popup' => 'Delete', 'class' => '');


$arr['modal']['modal_form'] = array('title' => 'Edit Broker', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_clone'] = array('title' => "<i class='icon copy'></i>Edit Broker (Clone)", 'class' => 'long', 'url' => 'form_edit.php?clone=1');
$arr['modal']['modal_form_clone']['button']['submit'] = array('title' => 'Clone & Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_clone']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');


$arr['modal']['modal_form_delete'] = array('title' => 'Remove', 'class' => 'small', 'url' => 'form_delete.php');