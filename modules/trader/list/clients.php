<?php

$arr['mysql'] = array(
    'table' => 'ssi_trader.clients 
            LEFT JOIN ssi_trader.servers ON clients.server_id = servers.server_id
            LEFT JOIN ssi_trader.broker ON clients.account = broker.user
            LEFT JOIN ssi_trader.orders ON clients.account = orders.account',
    'field' => "client_id, CONCAT(first_name, ' ', last_name) AS name, clients.account,
        email, created_at, clients.server_id, servers.name AS server_name, clients.server_id AS server_id, clients.broker_id, 
        broker.title AS broker_title, clients.token, daily_loss, total_loss",
    'limit' => 25,
    'group' => 'client_id',
    'like' => 'user'
);

$arr['list'] = array('id' => 'client', 'width' => '1200px', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['client_id'] = array('title' => "ID");
$arr['th']['name'] = array('title' => "Name");
$arr['th']['email'] = array('title' => "Email");
$arr['th']['created_at'] = array('title' => "<i class='clock icon'></i>Timestamp", 'align' => 'center');
$arr['th']['broker_title'] = array('title' => "Broker Name");
$arr['th']['account'] = array('title' => "Broker Account");
$arr['th']['daily_loss'] = array('title' => "Daily Loss");
$arr['th']['total_loss'] = array('title' => "Total Loss");



// Debugging kann aktiviert werden, um die SQL-Abfrage zu überprüfen
// $arr['mysql']['debug'] = true;

//$arr['th']['url'] = array('title' => "Url");

$arr['top']['button']['modal_form'] = array('title' => 'Create', 'icon' => 'plus', 'class' => 'blue circular');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue', 'popup' => 'Edit');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'popup' => 'Delete', 'class' => '');
$arr['tr']['button']['left']['modal_form2'] = array('title' => '', 'icon' => 'show sign in', 'class' => 'mini', 'popup' => 'Einloggen', 'onclick' => "impersonateUser('{token}')");

$arr['modal']['modal_form'] = array('title' => 'Edit', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Speichern', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Schließen', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_delete'] = array('title' => 'Remove', 'class' => 'small', 'url' => 'form_delete.php');