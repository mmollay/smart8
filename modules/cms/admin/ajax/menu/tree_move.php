<?php
include_once ('../../../../login/config_main.inc.php');
$site_id = ( int ) $_POST ['id'];
$page_id = $_SESSION ['smart_page_id'];
$parent_id = ( int ) $_POST ["parent"];
$old_parent_id = ( int ) $_POST ["old_parent"];
$pos = ( int ) $_POST ['position'];
$old_pos = ( int ) $_POST ['old_position'];

if ($old_parent_id != $parent_id)
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET parent_id = '$parent_id' WHERE site_id = '$site_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$set_position = 0;
// Reihenfolge auslesen und neu setzen
$position_query = $GLOBALS['mysqli']->query ( "SELECT site_id from smart_id_site2id_page WHERE page_id = '$page_id' and parent_id = '$parent_id' ORDER BY position " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $position_array = mysqli_fetch_array ( $position_query ) ) {
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET position = '$set_position' WHERE site_id = '{$position_array['site_id']}' AND page_id = '{$page_id}'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$set_position += 1;
}

if ($pos < $old_pos)
	// Position freigeben
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET position = position+1 WHERE position >= '$pos' AND page_id = '$page_id' AND parent_id = '$parent_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
else
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET position = position-1 WHERE position <= '$pos' AND page_id = '$page_id' AND parent_id = '$parent_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	
	// Neues Feld speichern
$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET parent_id = '$parent_id', position = '$pos' WHERE site_id = '$site_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

/*
 * //Position freigeben
 * $GLOBALS['mysqli']->query ("UPDATE menu SET position = position+1 WHERE position >= '$pos' AND menu_id = '$menu_id' AND parent_id = '$parent_id' ") or die (mysqli_error());
 *
 * //Neues Feld speichern
 * $GLOBALS['mysqli']->query("UPDATE menu SET parent_id = '$parent_id', position = '$pos' WHERE id = '$id' ") or die (mysqli_error());
 *
 * //Reihenfolge auslesen und neu setzen
 * $position_query = $GLOBALS['mysqli']->query ("SELECT id from menu WHERE menu_id = '$menu_id' and parent_id = '$parent_id' ORDER BY position ") or die(mysqli_error());
 * while ($position_array = mysqli_fetch_array($position_query)){
 * $GLOBALS['mysqli']->query ("UPDATE menu SET position = '$set_position' WHERE id = '{$position_array['id']}'") or die (mysqli_error());
 * $set_position += 1;
 * }
 */
set_update_site ( 'all' );
?>