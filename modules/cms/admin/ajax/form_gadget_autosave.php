<?php
// Datenbankverbindung herstellen

include_once ('../../../login/config_main.inc.php');

set_update_site ();
$gadget = $GLOBALS['mysqli']->real_escape_string ( $_POST['gadget'] );
// $value = $GLOBALS['mysqli']->real_escape_string ( $_POST['value'] );
$value = $_POST['value'];
$id = $GLOBALS['mysqli']->real_escape_string ( $_POST['id'] );
$layer_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['update_id'] );

$player = $_POST['player'];
$form_id = $_POST['form_id'];

/*
 * Guestbook addone - Eintragen in die Guestbook "smart_gadget_guestbook"
 */

// if ($id == 'code') {
// // Wandelt youtube_link in kurzcode um
// if ($player == 'youtube') {
// if (strlen ( $value ) > 12) {
// $value = parse_youtube ( $value );
// }
// } elseif ($player == 'vimeo') {
// // Wenn nur Code eingegeben wird, sonst umwandeln
// if (! is_numeric ( $value )) {
// $value = parse_vimeo ( $$value );
// }
// }
// }

if ($gadget == 'button') {
	$array = preg_split ( '/(?<=\D)(?=\d)|\d+\K/', $id );
	$i = $array['1'];
	$field_id = $array['0'];
	$button_value = $value;
	
	if ($field_id == 'button_text')
		$button_id = 'title';
	elseif ($field_id == 'button_icon')
		$button_id = 'icon';
	elseif ($field_id == 'button_color')
		$button_id = 'color';
	elseif ($field_id == 'button_url')
		$button_id = 'url';
	elseif ($field_id == 'button_link')
		$button_id = 'link';
	elseif ($field_id == 'button_tooltip')
		$button_id = 'tooltip';
	elseif ($field_id == 'button_target')
		$button_id = 'target';
	
	if ($button_id) {
		// Check ob dieser Button schon vorhanden ist, wenn nicht wird dieser angelegt
		$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_button WHERE layer_id = '$layer_id' and sequence = '$i'" );
		$array = mysqli_fetch_array ( $query );
		if (! is_array ( $array )) {
			$GLOBALS['mysqli']->query ( "INSERT INTO smart_gadget_button  
				SET $button_id = '$button_value', 
					sequence = '$i',
					layer_id = '$layer_id'  " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		} else {
			$GLOBALS['mysqli']->query ( "
		UPDATE smart_gadget_button 
			SET $button_id = '$button_value'
				WHERE layer_id = '$layer_id' and  sequence = '$i' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		}
	}
}

// $button_text = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_text$i"] );
// $button_icon = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_icon$i"] );
// $button_color = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_color$i"] );
// $button_url = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_url$i"] );
// $button_link = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_link$i"] );

// array_layout aus der Datenbank auslesen

$dynamic_modus = $GLOBALS['mysqli']->real_escape_string ( $_POST['dynamic_modus'] );
$dynamic_name = $GLOBALS['mysqli']->real_escape_string ( $_POST['dynamic_name'] );

if ($id == 'dynamic_modus') {
	$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET dynamic_modus = '$value' WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}

if ($id == 'dynamic_name')
	$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET dynamic_name = '$value' WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
else {
	$sql = $GLOBALS['mysqli']->query ( "SELECT gadget_array,format,dynamic_modus,dynamic_name from smart_layer WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array = mysqli_fetch_array ( $sql );
	$gadget_array = generate_array ( $id, $value, $array[0] );

	//Save option_value for element 
	save_smart_element_option($layer_id, array($id=>$value));
	
	// Erzeugt ein Array und fügt neue Werte hinzu
	$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET gadget_array = '$gadget_array' WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}

if ($id == 'code' && $player) {
	echo $value;
} else {
	//set_update_site (); ->> wird in layer_new_php gesetzt
	include ('layer_new_inc.php');
}

?>