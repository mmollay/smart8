<?php
// Verbindung zur Datenbank herstellen
include_once ('../../../login/config_main.inc.php');
require ('../../config.inc.php');
include ('../inc/function_del.inc.php');

$array = preg_split ( '/trash/', $_POST ['id'] );
$layer_id = $array [1];

$abfrage = del_layer ( $layer_id );

for($i = 0; $i < count ( $abfrage ); $i ++) {
	// echo $abfrage[$i];
	$GLOBALS['mysqli']->query ( $abfrage [$i] ) or die ( mysqli_error ($GLOBALS['mysqli']) );
}

set_update_site();


?>