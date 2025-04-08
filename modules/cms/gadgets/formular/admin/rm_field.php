<?php
include ('../../../../login/config_main.inc.php');

$value = $GLOBALS['mysqli']->real_escape_string ( $_POST['value'] );
$id = $GLOBALS['mysqli']->real_escape_string ( $_POST['id'] );
$id = preg_replace ( '[row_field-]', '', $id );
$site_id = $_SESSION['site_id'];


// Prüfen ob der User die Berechtigung hat auf dieser Seite was zu ändern
$query = $GLOBALS['mysqli']->query ( "SELECT site_id FROM smart_layer a LEFT JOIN smart_formular b ON a.layer_id = b.layer_id WHERE field_id = '$id' and site_id = $site_id " );
$check = mysqli_num_rows ( $query );

if ($check) {
	$GLOBALS['mysqli']->query ( "DELETE FROM smart_formular WHERE field_id = '$id' LIMIT 1 " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo "ok";
	set_update_site();
}