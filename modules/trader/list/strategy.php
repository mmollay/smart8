<?php
$arr['mysql'] = array(
    'table' => 'ssi_trader.hedging_group',
    'field' => "group_id, title, IF(LENGTH(text) > 30, CONCAT(LEFT(text, 30), '..'), text) as text, timestamp, reverse",
    'limit' => 25,
    'group' => 'group_id',
    'like' => 'title'
);
// $arr['mysql']['debug'] = true;

$arr['list'] = array('id' => 'strategy', 'width' => '800px', 'size' => 'small', 'class' => 'compact celled striped definition');

$arr['th']['group_id'] = array('title' => "ID");
$arr['th']['title'] = array('title' => "Strategy");
$arr['th']['text'] = array('title' => "Description");
$arr['th']['timestamp'] = array('title' => "<i class='clock icon'></i>Timestamp", 'align' => 'center');
$arr['top']['button']['modal_form'] = array('title' => 'Anlegen', 'icon' => 'plus', 'class' => 'blue circular');

$arr['tr']['buttons']['left'] = array('class' => 'tiny');
$arr['tr']['button']['left']['modal_form'] = array('title' => '', 'icon' => 'edit', 'class' => 'blue', 'popup' => 'Edit');
$arr['tr']['button']['left']['modal_form_clone'] = array('icon' => 'copy', 'popup' => 'Cloning');
//Button zum senden von der Strategie an den Server
$arr['tr']['button']['left']['modal_form_send'] = array('icon' => 'send', 'popup' => 'Send to Server', 'class' => 'orange');

$arr['tr']['buttons']['right'] = array('class' => 'tiny');
$arr['tr']['button']['right']['modal_form_delete'] = array('title' => '', 'icon' => 'trash', 'popup' => 'Delete', 'class' => '');

$arr['modal']['modal_form_clone'] = array('title' => "<i class='icon copy'></i>Edit Hedging-strategy (Clone)", 'class' => 'scrolling', 'url' => 'form_edit.php?clone=1', 'focus' => true);
$arr['modal']['modal_form_clone']['button']['submit'] = array('title' => 'Clone & Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form_clone']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form'] = array('title' => 'Edit Hedging-strategy', 'class' => 'scrolling', 'url' => 'form_edit.php', 'focus' => true);
$arr['modal']['modal_form']['button']['submit'] = array('title' => 'Save', 'color' => 'green', 'form_id' => 'form_edit'); // form_id = > ID formular
$arr['modal']['modal_form']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');

$arr['modal']['modal_form_send'] = array('title' => 'Stratey - Sending', 'class' => 'long', 'url' => 'form_send_strategy2server.php', 'focus' => true);
$arr['modal']['modal_form_send']['button']['cancel'] = array('title' => 'Close', 'color' => 'grey', 'icon' => 'close');
$arr['modal']['modal_form_send']['button']['submit'] = array('title' => 'Send to Servers', 'color' => 'green', 'form_id' => 'send_strategy', 'icon' => 'send'); // form_id = > ID formular

$arr['modal']['modal_form_delete'] = array('title' => 'Remove', 'class' => 'small', 'url' => 'form_delete.php');