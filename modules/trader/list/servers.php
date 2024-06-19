<?php
$arr['mysql'] = array(
    'table' => 'ssi_trader.servers a '
        . 'LEFT JOIN ssi_trader.hedging_group b ON a.strategy_id = b.group_id '
        . 'LEFT JOIN ssi_trader.broker c ON a.broker_id = c.broker_id', // Verknüpfung mit der broker-Tabelle
    'field' => "a.server_id, a.url, a.name, LEFT(a.description, 30) AS short_description, "
        . "a.timestamp, b.title as strategy_title, a.lotsize, a.active, "
        . "c.title as broker_title, strategy_default, contract_default", // Verwendung von COALESCE für den Titel
    'limit' => 25,
    'group' => 'a.server_id',
    'like' => 'a.name', // Suchfunktion auf 'name' anpassen
    //'where' => "AND a.user_id =" . $_SESSION['user_id']
);

// $arr['mysql']['debug'] = true;


$arr['list'] = array('id' => 'server', 'width' => '1200px', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['server_id'] = array('title' => "ID", 'width' => '70px');
$arr['th']['active'] = array('title' => "Active", 'align' => 'center', 'width' => '50px');
$arr['th']['name'] = array('title' => "Server Name", 'width' => '170px');
$arr['th']['url'] = array('title' => "URL");
//$arr['th']['domain'] = array('title' => "Domain");
$arr['th']['strategy_default'] = array('title' => "Strategy");
$arr['th']['contract_default'] = array('title' => "Contract");
$arr['th']['lotsize'] = array('title' => "Lotsize");
$arr['th']['broker_title'] = array('title' => "Broker");
// $arr['th']['short_description'] = array('title' => "Description"); // 'align' kann angepasst werden
$arr['th']['timestamp'] = array('title' => "<i class='clock icon'></i>Timestamp", 'align' => 'center');

$arr['top']['button']['modal_form'] = array('title' => 'Create', 'icon' => 'plus', 'class' => 'blue circular');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue', 'popup' => 'Edit');
$arr['tr']['button']['left']['modal_form_clone'] = array('icon' => 'copy', 'popup' => 'Klonen');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'popup' => 'Delete', 'class' => '');

$arr['modal']['modal_form_clone'] = array('title' => "<i class='icon copy'></i>Edit Server (Clone)", 'class' => 'long', 'url' => 'form_edit.php?clone=1');
$arr['modal']['modal_form_clone']['button']['submit'] = array('title' => 'Clone & Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_clone']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form'] = array('title' => 'Edit Server', 'class' => '', 'url' => 'form_edit.php');
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_delete'] = array('title' => 'Remove', 'class' => 'small', 'url' => 'form_delete.php');