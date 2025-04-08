<?php
if (! $color)
	$color = 'yellow';

if (! $width)
	$width = '100';

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'four fields' );
$arr['field']['diameter'] = array ( 'tab' => 'first' , 'label' => "Größe" , 'type' => 'input' , 'value' => $diameter ,  'label_right' => 'px' );
$arr['field']['align'] = array ( 'tab' => 'first' , 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
$arr['field']['info_position'] = array ( 'tab' => 'first' , 'label' => "Info" , "type" => "select" , 'array' => array ( 'top' => 'Oben' , 'right' => 'Rechts' , 'bottom' => 'Unten' , 'left' => 'Links' , 'popup' => 'Popup' ) , 'value' => $info_position );
$arr['field']['color'] = array ( 'tab' => 'first' , group => 1 , 'label' => Mondfarbe , 'type' => 'color' , 'size' => 10 , 'value' => $color );