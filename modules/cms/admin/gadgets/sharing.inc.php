<?php
$arr['field']['align'] = array ( 'tab' => 'first' , 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );

$arr['field']["text"] = array ( 'tab' => 'first' , 'label' => 'Titel' , 'type' => 'input' , 'value' => $text , 'info' => 'Wird das Feld leer gelassen, dann wird die aktuelle Seite verwendet' );
$arr['field']["url"] = array ( 'tab' => 'first' , 'label' => 'Link' , 'type' => 'input' , 'value' => $url , 'placeholder' => 'https://' );
// $arr['field']['logo'] = array ( 'tab' => 'first' , "label" => "Bilderpfad" , "type" => "finder" , 'value' => $logo , 'placeholder' => 'https://www.' );

$array_social = array ( "facebook" , "whatsapp" , "twitter" , "googleplus" , "linkedin" , "pinterest" , "stumbleupon" , "pocket" , "viber" , "messenger" , "vkontakte" , "telegram" , "line" , "rss" , "email" );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
foreach ( $array_social as $value ) {
	$arr['field']["social_$value"] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => $value , 'value' => ${"social_$value"} );
}
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$array_theme = array ( 'jssocials-theme-classic' => 'jssocials-theme-classic' , 'jssocials-theme-flat' => 'jssocials-theme-flat' , 'jssocials-theme-minima' => 'jssocials-theme-minima' , 'jssocials-theme-plain' => 'jssocials-theme-plain' );

$arr['field']['jssocials_theme'] = array ( 'tab' => 'first' , 'label' => "Design" , "type" => "dropdown" , 'array' => $array_theme , 'value' => $jssocials_theme );

$arr['field']["showLabel"] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Label anzeigen' , 'value' => $showLabel );
$arr['field']["showCount"] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Counter anzeigen' , 'value' => $showCount );
$arr['field']["popup"] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Als Popup öffnen' , 'value' => $popup );

$arr['field']['fontsize'] = array ('tab' => 'first' ,  'label' => 'Gr&ouml;ße' , 'type' => 'slider' , 'min' => 10 , 'max' => 40 , 'step' => 1 , 'unit' => 'px' , 'value' => $fontsize );