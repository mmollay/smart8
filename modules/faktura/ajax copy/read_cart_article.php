<?php
/*
 * Cart in SESSION SPEICHERN
 */
error_reporting(E_ALL ^  E_NOTICE);
session_start ();
$array = array ( 'temp_id' , 'format' , 'count' , 'art_nr' , 'art_title' , 'art_text' , 'account' , 'netto', 'select_temp' );

foreach ( $array as $id => $key ) {
	$value = $_SESSION['temp_cart'][$_POST['id']][$key];
	$value = str_replace ( "\n", "\\n", $value );
	
	if ($key == 'account' ) {
		// Dropdown
		echo "$('#dropdown_$key').dropdown('set selected', '$value');";
	} else {
		// else
		echo "$('#$key').val('$value');";
	}
}
?>