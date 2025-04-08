<?php
include_once ('../../../../login/config_main.inc.php');
$id = $_POST ['id'];
$text = $_POST ['text'];
//echo $_SESSION ['lang'];
// Speichert den Platz des Layers
$GLOBALS['mysqli']->query ( "UPDATE smart_langSite SET menu_text = '$text' WHERE fk_id = '$id' and lang = '{$_SESSION['page_lang']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
set_update_site ( 'all' );
?>