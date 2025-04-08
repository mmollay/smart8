<?php
if (! $button)
	$button = 'Anmelden';
if (! $align)
	$align = 'right';
	$arr['field']['icon'] = array ( 'tab' => 'first' , 'label' => "Icon" , 'type' => 'icon' , 'value' => $icon  ); //, 'array_icon' => array ( 'sign in' , 'alarm' )
$arr['field']['button_text'] = array ( 'tab' => 'first' , "label" => "Anmeldebutton" , "type" => "input" , 'value' => $button );
$arr['field']['color'] = array ( 'tab' => 'first' , 'label' => "Farbe" , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $color );
$arr['field']['align'] = array ( 'tab' => 'first' , 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
		