<?php
//mm@ssi.at 21.Feb.2024 
//Wird derzeit nicht mehr benötigt 

include ('../t_config.php');
include ('../../ssi_smart/smart_form/include_form.php');

$sql_broker_server =
    'SELECT broker_id, CONCAT(broker_server, "<br>", name) as title
    FROM ssi_trader.broker a LEFT JOIN ssi_trader.servers b ON a.server_id = b.server_id  
        WHERE a.user_id = ' . $_SESSION['user_id'] . ' 
            ORDER BY broker_server ASC
            ';

//Hedging Strategy
$arr['form'] = array('action' => "ajax/form_edit2.php", 'id' => 'form_setting', 'class' => 'center segment', 'width' => '800');
$arr['sql'] = array('query' => "SELECT * from ssi_trader.setting WHERE user_id  = " . $_SESSION['user_id']);
$arr['ajax'] = array('success' => "after_form_setting(data)", 'dataType' => "html");
$arr['field']['title'] = array('type' => 'input', 'label' => 'Title', 'focus' => true);
//$arr ['field'] ['text'] = array ('type' => 'textarea','label' => 'Description' );
$arr['field']['strategy_id'] = array('type' => 'dropdown', 'label' => 'Primary Hedging Strategy', 'array_mysql' => 'SELECT group_id, title FROM ssi_trader.hedging_group  ORDER BY title ASC', 'text' => 'title', 'class' => 'fluid search selection');
$arr['field']['broker_id'] = array('type' => 'dropdown', 'label' => 'Broker - Server', 'array_mysql' => $sql_broker_server, 'text' => 'title', 'class' => 'fluid search selection');

$arr['button']['submit'] = array('value' => "Execute", 'color' => 'blue');
$output = call_form($arr);
echo $output['html'] . $output['js'] . "<br>";

echo "<script type=\"text/javascript\" src=\"js/form_setting.js\"></script>";
