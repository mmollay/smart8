<?php
include ('../t_config.php');
include (__DIR__ . '/../../../../smartform/include_form.php');

$arr['form'] = array('action' => "ajax/post.php", 'id' => 'send_strategy', 'inline' => 'list');
$arr['ajax'] = array('success' => "after_form_setting(data)", 'dataType' => "json");
$arr['hidden']['createStrategy'] = $_POST['update_id'];

$strategy_id = $_POST['update_id'];
$strategy = fetchStrategy($mysqli, $strategy_id);
echo "<h2>{$strategy['title']}</h2><hr>";

//Auflistung aller Server zur Auswahl mit checkboxen
$servers = fetchAllServers($mysqli);
foreach ($servers as $i => $server) {
    $arr['field']["server{$server['server_id']}"] = array('type' => 'checkbox', 'label' => $server['UrlName'], 'value' => 1);
}

$output = call_form($arr);

echo $output['html'];
echo $output['js'];

echo $add_js;