<?php 
if (!$icon) $icon = 'lemon';
if (!$size) $size = 'huge';
$arr['field']['icon'] = array ( 'tab' => 'first' , 'label' => "Icon" , 'type' => 'icon' , 'value' => $icon );
$arr['field']['color'] = array ( 'tab' => 'first' , 'label' => "Darstellung" , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $segment_color , 'placeholder' => 'Farben' , 'value' => $color );
$arr['field']['size'] = array ( 'tab' => 'first' , 'id' => 'size' , 'label' => 'Größe' , 'type' => 'dropdown' , "array" => $array_size , 'value' => $size );