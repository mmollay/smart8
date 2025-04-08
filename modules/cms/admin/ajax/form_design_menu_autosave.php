<?php
// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');

$layer_id = $_POST['layer_id'];
$id = $_POST['id'];
$value = $_POST['value'];

// array_layout aus der Datenbank auslesen
$sql = $GLOBALS['mysqli']->query ( "SELECT gadget_array FROM smart_layer WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

$array = mysqli_fetch_array ( $sql );
$array_layout = $array[0];
$layout_array = generate_array ( $id, $value, $array_layout );
// Erzeugt ein Array und fügt neue Werte hinzu
$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET gadget_array = '$layout_array' WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

set_update_site ( 'all' );

//Design-daten werden neu ausgelesen
$sql = $GLOBALS['mysqli']->query ( "SELECT gadget_array FROM smart_layer WHERE layer_id = '$layer_id' " );
$array = mysqli_fetch_array ( $sql );
$gadget_array = $array['gadget_array'];
$gadget_array_n = explode ( "|", $gadget_array );
if ($gadget_array) {
	foreach ( $gadget_array_n as $array_split ) {
		$array2 = preg_split ( "[=]", $array_split, 2 );
		$GLOBALS[$array2[0]] = $array2[1];
	}
}

// Erzeugt css und übergib es in das System
include (__DIR__ . '/../../gadgets/menu/css.php');
echo $set_style;