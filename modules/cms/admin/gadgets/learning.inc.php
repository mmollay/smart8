<?php

// Array - Thema
$query = $GLOBALS ['mysqli']->query ( "SELECT * from ssi_learning.learn_theme WHERE user_id = '{$_SESSION['user_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$title = $array ['title'];
	$theme_id = $array ['theme_id'];
	$array_theme [$theme_id] = $title;
}

// Array - Gruppe
$query = $GLOBALS ['mysqli']->query ( "SELECT * from ssi_learning.learn_group WHERE theme_id = '$theme_id' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$group_id = $array ['group_id'];
	$title = $array ['title'];
	$array_group [$group_id] = $title;
}

if (! $correctness_percent)
	$correctness_percent = 100;

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'ui accordion' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','text' => "<div class='title active'><i class='icon dropdown'></i>Allgemein</div>" );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'active content' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'ui message' );
$arr ['field'] ['learning_theme_id'] = array ('tab' => 'first','label' => 'Thema wöhlen','type' => 'dropdown','array' => $array_theme,'placeholder' => 'Thema wählen','value' => $learning_theme_id );
$arr ['field'] ['learning_group_id'] = array ('tab' => 'first','label' => 'Gruppe wöhlen','type' => 'dropdown','array' => $array_group,'placeholder' => 'Gruppe wählen','value' => $learning_group_id );
$arr ['field'] ['correctness_percent'] = array ('class' => 'no_reload_element','tab' => 'first','label' => 'Zu erreichende Prozent','type' => 'slider','min' => 0,'max' => 100,'step' => 1,'value' => $correctness_percent,'unit' => '%' );
$arr ['field'] ['show_message_right_or_wrong'] = array ('tab' => 'first','label' => 'Richtig/Falsch anzeigen','info' => 'Es kann angetzeigt werden ob eine Antwort richtig oder falsch war','type' => 'checkbox','value' => $show_message_right_or_wrong );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'content' , 'class' => 'header ui green message ', 'text'=>'Bei Erfolg' ); // fields two
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui bottom attached message' ); // fields two
// $arr['field']["url"] = array ( 'tab' => 'first' , 'label' => 'Bild verlinken mit Seite' , 'type' => 'dropdown' , 'array' => $array_sites , 'value' => $url );
// $arr['field']["link"] = array ( 'tab' => 'first' , 'label' => 'ODER (externer Link)' , 'type' => 'input' , 'value' => $link , 'placeholder' => 'http://' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','text' => "<div class='title'><i class='icon green dropdown'></i>Bei Erfolg</div>" );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'content' );
$arr ['field'] ['success_text'] = array ('title' => 'Bei Erfolg','tab' => 'first','type' => 'ckeditor_inline','toolbar' => 'mini','value' => $success_text );
$arr ['field'] ['success_button'] = array ('tab' => 'first','type' => 'button','class_button' => 'mini blue','value' => 'Text übernehmen','onclick' => "save_value_element('$update_id','success_text',$('#success_text').html(),'learning');" );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div','text' => "<div class='title'><i class='icon red dropdown'></i>Bei Nicht-Erfolg</div>" );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div','class' => 'content' );
$arr ['field'] ['error_text'] = array ('title' => 'Bei Erfolg','tab' => 'first','type' => 'ckeditor_inline','toolbar' => 'mini','value' => $error_text );
$arr ['field'] ['error_button'] = array ('tab' => 'first','type' => 'button','class_button' => 'mini blue','value' => 'Text übernehmen','onclick' => "save_value_element('$update_id','error_text',$('#error_text').html(),'learning');" );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );
$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );

$arr ['field'] [] = array ('tab' => 'first','type' => 'div_close' );
?>