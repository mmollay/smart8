<?php
include_once ('../../../../login/config_main.inc.php');
$id = $_POST ['id'];
// Speichert den Platz des Layers
$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET menu_disable = 0 WHERE site_id ='$id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

include_once (__DIR__ . '/../../library/function_menu.php');
$menuData = generateMenuStructure ( $_SESSION[smart_page_id],true );
echo buildMenuAdmin ( 0, $menuData );

// $GLOBALS['mysqli']->query("DELETE FROM smart_id_site2id_page WHERE site_id` ='$id' LIMIT 1") or die(mysqli_error());
set_update_site ( 'all' );
?>