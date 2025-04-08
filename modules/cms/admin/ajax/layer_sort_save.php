<?php

// Verbindung zur Datenbank herstellen
include_once ('../../../login/config_main.inc.php');

$sort = $_POST['sort'];
$array_position = preg_split ( "/_/", $_GET['id_position'] );

$position = $array_position['0'];
$splitter_layer_id = $array_position['1'];

// echo $id_position;

for($i = 0; $i < count ( $sort ); $i ++) {
	// echo "\nUPDATE `tbl_layer` SET `sort`='$i', position = '$position' WHERE `layer_id`='{$sort[$i]}' ";
	if ($splitter_layer_id != $sort[$i]) {
		$GLOBALS['mysqli']->query ( "UPDATE `smart_layer` SET 
		`sort`='$i', 
		splitter_layer_id = '$splitter_layer_id' , 
		page_id = '{$_SESSION['smart_page_id']}',
		site_id = '{$_SESSION['site_id']}', 
		position = '$position' 
			WHERE `layer_id`='{$sort[$i]}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	}
	// Auslesen der Paramenter für Module
	$query = $GLOBALS['mysqli']->query ( "SELECT gadget,gadget_array FROM smart_layer WHERE layer_id = '{$sort[$i]}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array_main = mysqli_fetch_array ( $query );
	
	
	$gadget_array = $array_main['gadget_array'];
	
	$gadget_array_n = explode ( "|", $gadget_array );
	if ($array_main['gadget_array']) {
		foreach ( $gadget_array_n as $array ) {
			$array2 = preg_split ( "[=]", $array, 2 );
			${$array2[0]} = $array2[1];
		}
	}
	
	
	// Wenn eine Gallerie verschoben wird, dann wird diese neu geladen, damit die Darstellung passt!
	if ($array_main['gadget'] == 'gallery' and !$set_modul_gallery) {
		$set_modul_gallery = true;
		echo "$('#flex-images{$sort[$i]}').flexImages({rowHeight: $rowHeight}); $('.popup').popup();";
		//echo "$('#flex-images{$sort[$i]}').flexImages({rowHeight: $rowHeight});";
	}
}

if (in_array ( $position, array ( 'header' , 'footer' ) ))
	$all = 'all';

set_update_site ( $all );
?>