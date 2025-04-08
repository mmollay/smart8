<?php
include ('../mysql_map.inc.php');

// Speichert Sucheintrag und übergibt in Session
if (isset ( $_POST['value'] )) {
	$_SESSION["map_filter"][$_POST['id']] = $_POST['value'];
} else {
	
	// Hier wird der Rest übergeben
	if ($_SESSION["map_filter"]['map_zip'] != $_POST['map_zip'])
		$_SESSION["map_filter"]['map_places'] = '';
	elseif ($_POST['map_places'])
		$_SESSION["map_filter"]['map_places'] = $_POST['map_places'];
	
	if ($_POST['map_zip'])
		$_SESSION["map_filter"]['map_zip'] = $_POST['map_zip'];
	
	$_SESSION["map_filter"]['not_defined'] = $_POST['not_defined'];
	$_SESSION["map_filter"]['set_admin'] = $_POST['set_admin'];
	$_SESSION["map_filter"]['autofit'] = $_POST['autofit'];
	$_SESSION["map_filter"]['bicyclinglayer'] = $_POST['bicyclinglayer'];
	
	setcookie ( "autofit", $_SESSION["map_filter"]['autofit'], time () + 3600 );
	setcookie ( "bicyclinglayer", $_SESSION["map_filter"]['bicyclinglayer'], time () + 3600 );
	setcookie ( "not_defined", $_SESSION["map_filter"]['not_defined'], time () + 3600 );
	setcookie ( "set_admin", $_SESSION["map_filter"]['set_admin'], time () + 3600 );
	
	// Sorten auslesen
	$query = $GLOBALS['mysqli']->query ( "SELECT tree_group_id FROM tree_group" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	while ( $array2 = mysqli_fetch_array ( $query ) ) {
		$tree_group_id = $array2['tree_group_id'];
		$_SESSION["map_filter_fruit"][$tree_group_id] = $_POST['fruit_' . $tree_group_id];
	}
}

if ($_SESSION["map_filter"]['bicyclinglayer'])
	$js_map_bicyclinglayer = true;

if ($_SESSION["map_filter"]['autofit'])
	$js_map_autofit = true;

echo "loadMap('$js_map_autofit','$js_map_bicyclinglayer'); loadMenu();";

// exit ();

// $session_type = $_POST['type'];
// if (isset ( $_POST['value'] ) && $session_type) {
	
// 	// Uebergabe der Fruit-Checkboxen
// 	if ($session_type == 'fruit') {
// 		$_SESSION["map_filter_fruit"][$_POST['id']] = $_POST['value'];
// 		// Klassische Suchparamenter (INPUT, SELECT)
// 	} else {
// 		$_SESSION["map_filter"][$_POST['id']] = $_POST['value'];
// 	}
// 	// Für alle Selectboxen
// } elseif (isset ( $_POST['value'] )) {
// 	$_SESSION["map_filter"][$_POST['id']] = $_POST['value'];
// }
