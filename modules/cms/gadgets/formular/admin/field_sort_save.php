<?php
// Verbindung zur Datenbank herstellen
include ('../../../../login/config_main.inc.php');

$sort = $_POST['row_field'];

print_r ($_POST['row_field']);

$array_position = preg_split ( "/_/", $_GET['id_position'] );
$position = $array_position['0'];
$splitter_field_id = $array_position['2'];

for($i = 0; $i < count ( $sort ); $i ++) {
	if ($splitter_field_id != $sort[$i])
		// Execute statement:
		$GLOBALS['mysqli']->query ( "UPDATE `smart_formular` SET `sort`='$i' , splitter_field_id = '$splitter_field_id' , position = '$position' WHERE `field_id`='{$sort[$i]}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

}

set_update_site ();
?>