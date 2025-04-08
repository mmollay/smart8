<?php
// Verbindung zur Datenbank herstellen
include_once ('../../../login/config_main.inc.php');

$id = $GLOBALS['mysqli']->real_escape_string ( $_POST['id'] );
if ($id)
	$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET archive ='' WHERE layer_id = '$id' " ) or die (mysqli_error());

	set_update_site();
	
//übergabe der aktuellen Seite
echo  mysql_singleoutput ( "SELECT site_id FROM smart_layer WHERE layer_id = $id LIMIT 1" );