<?php

if ($_GET['clone']) {
    $addCloneName = " (Clone)";
    $arr['field']['clone'] = array('type' => 'hidden', 'value' => '1');
}

$arr['ajax'] = array('success' => "afterFormSubmit(data)", 'dataType' => "html");
// $arr['tab'] = array('tabs' => array(1 => 'Default', 2 => 'More'), 'active' => '1');
$arr['sql'] = array('query' => "SELECT *,  CONCAT(title,'$addCloneName') title from ssi_trader.broker WHERE broker_id  = '{$_POST['update_id']}'");
$arr['field']['title'] = array('tab' => '1', 'type' => 'input', 'label' => 'Matchcode (Title)', 'focus' => true);
//Checkbox für Realaccount
$arr['field']['real_account'] = array('tab' => '1', 'type' => 'checkbox', 'label' => 'Real Account');
$arr['field']['broker_server'] = array('tab' => '1', 'type' => 'input', 'label' => 'Broker-Server', 'focus' => true, 'placeholder' => 'BlackBullMarkets-Demo');
$arr['field']['user'] = array('tab' => '1', 'type' => 'input', 'label' => 'Account (User)');
$arr['field']['password'] = array('tab' => '1', 'type' => 'password', 'label' => 'Password');


$add_js .= "<script type=\"text/javascript\" src=\"js/form_after.js\"></script>"; // Ensure the file name is correct
