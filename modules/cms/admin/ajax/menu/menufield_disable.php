<?php
include_once ('../../../../login/config_main.inc.php');
$id = $_POST ['id'];
// Speichert den Platz des Layers
$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET menu_disable = 1 WHERE site_id ='$id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

// $GLOBALS['mysqli']->query("DELETE FROM smart_id_site2id_page WHERE site_id` ='$id' LIMIT 1") or die(mysqli_error());
set_update_site ( 'all' );
?>