<?php
include_once ("../../gadgets/map/config_map.php");
$arr['field']['height'] = array ( 'tab' => 'first' , "type" => "input" , "label" => "Höhe" , 'value' => $height , 'class' => 'dimension_custorm six wide' );
$arr['field']['destination'] = array ( 'tab' => 'first' , "type" => "select" , "label" => "Stadt wählen", 'info'=>'Diese wird standardmässig geladen' , 'value' => $destination , "array" => $array_city );
//$arr['field']['show_clients'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Baumpaten in Liste anzeigen' , 'value' => $show_clients );
//$arr['field']['show_sorts'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Sorten anzeigen' , 'value' => $show_sorts );
?>