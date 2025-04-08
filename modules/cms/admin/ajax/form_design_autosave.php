<?php
// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');

$page_id = $_SESSION['smart_page_id'];
$id = $_POST['id'];
$value = $_POST['value'];

if ($page_id) {
	
	if ($id == 'style') {
		$GLOBALS['mysqli']->query ( 'UPDATE smart_layout SET style = "' . $value . '" WHERE page_id = "' . $page_id . '"' ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		// exit ();
	} elseif ($id == 'jquery') {
		$GLOBALS['mysqli']->query ( 'UPDATE smart_layout SET jquery = "' . $value . '" WHERE page_id = "' . $page_id . '"' ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		// echo "ok";
		exit ();
	}
	
	// array_layout aus der Datenbank auslesen
	$sql = $GLOBALS['mysqli']->query ( "SELECT layout_array FROM smart_layout WHERE page_id = $page_id " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	
	$array = mysqli_fetch_array ( $sql );
	$array_layout = $array[0];
	$layout_array = generate_array ( $id, $value, $array_layout );
	// Erzeugt ein Array und fügt neue Werte hinzu
	$GLOBALS['mysqli']->query ( "UPDATE smart_layout SET layout_array = '$layout_array' WHERE page_id = $page_id" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	
	set_update_site ( 'all' );
}

// Erzeugt css und übergib es in das System
include ("../../inc/load_css.php");
// include ("../../library/css_umwandler.inc");
// echo css_umwandeln ( $set_style );

echo $set_style;