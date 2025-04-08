<?php
// $arr['field']['align'] = array ( 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
if (! $format)
	$format = '[[clock]]';
$arr['field']['format'] = array ( 'tab' => 'first' , 'type' => 'ckeditor_inline' , 'toolbar' =>'mini' , 'value' => $format ,  'focus' => true );
$arr['field']['infotext'] = array ( 'tab' => 'first' , 'type' => 'text' , 'label' => 'Platzhalter: <b>[[clock]]</b>' );
$arr['field'][''] = array ( 'tab' => 'first' , 'type' => 'button' , 'class_button' => 'mini blue' , 'value' => 'Text übernehmen' , 'onclick' => "save_value_element('$update_id','format',$('#format').html(),'marquee');" );
	