<?php
if (! $format)
	$format = '[[counter]]';

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['date'] = array ( 'tab' => 'first' , 'label' => "Datum" , 'type' => 'date' , 'value' => $date , 'icon' => 'calendar' );
$arr['field']['time'] = array ( 'tab' => 'first' , 'label' => "Uhrzeit" , 'type' => time , 'value' => $time , 'icon' => 'time' , 'placeholder' => '10:00' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['format'] = array ( 'tab' => 'first' , 'type' => 'ckeditor_inline' , 'toolbar' =>'mini' , 'value' => $format );
$arr['field']['infotext'] = array ( 'tab' => 'first' , 'type' => 'text' , 'label' => 'Platzhalter: <b>[[counter]]</b>' );
$arr['field'][''] = array ( 'tab' => 'first' , 'type' => 'button' , 'class_button' => 'mini blue' , 'value' => 'Text übernehmen' , 'onclick' => "save_value_element('$update_id','format',$('#format').html(),'marquee');" );
	