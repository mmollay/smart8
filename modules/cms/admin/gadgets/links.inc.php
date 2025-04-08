<?php
if (! $vwidth)
	$vwidth = 1200;
if (! $vheight)
	$vheight = 800;
if (! $size)
	$size = 'small';

$arr['field']['text'] = array ( 'tab' => 'first' , "label" => "Linkliste" , 'type' => 'textarea' ,  'focus' => true , 'value' => $text );
$arr['field']['size'] = array ( 'tab' => 'first' , 'id' => 'size' , 'label' => 'Größe' , 'type' => 'dropdown' , "array" => $array_size , 'value' => $size );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'header' , 'text' => 'Bildschirmgröße' , 'class' => 'dividing small' , 'info' => 'Standardeinstellung sollte 1200px * 800px nicht überschreiten um ein optimales Ergebnisse bei der Ausgabe zu bekommen.' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'three fields' );
$arr['field']['vwidth'] = array ( 'tab' => 'first' , "label" => "Breite" , 'label_right' => 'px' , "type" => "input" , 'value' => $vwidth );
$arr['field']['vheight'] = array ( 'tab' => 'first' , "label" => "Höhe" , "type" => "input" , 'label_right' => 'px' , 'value' => $vheight );
$arr['field']['align'] = array ( 'tab' => 'first' , 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
			
			// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
			// $arr['field']['align'] = array ( 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
			