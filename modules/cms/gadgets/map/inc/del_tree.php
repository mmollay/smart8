<?php
include ('../mysql_map.inc.php');

$tree_id = $GLOBALS['mysqli']->real_escape_string ( $_POST[tree_id] );
//Es wird auf Mülleimber gesetzt und nicht mehr gelöscht
//$GLOBALS['mysqli']->query ( "DELETE from tree where tree_id =$tree_id Limit 1" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
if ($tree_id) {
	$GLOBALS['mysqli']->query ( "UPDATE tree SET trash = 1  where tree_id = '$tree_id' Limit 1" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}
echo "ok";
