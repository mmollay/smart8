<?php
$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");
// $arr['tab'] = array('tabs' => array(1 => 'Default', 2 => 'More'), 'active' => '1');
$arr['sql'] = array('query' => "SELECT * from ssi_trader.account WHERE account_id  = '{$_POST['update_id']}'");
$arr['field']['server'] = array('tab' => '1', 'type' => 'input', 'label' => 'Server', 'focus' => true);
$arr['field']['user'] = array('tab' => '1', 'type' => 'input', 'label' => 'Account');
$arr['field']['password'] = array('tab' => '1', 'type' => 'password', 'label' => 'Password');

$add_js .= "<script type=\"text/javascript\" src=\"js/form_after.js\"></script>";