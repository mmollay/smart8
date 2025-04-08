<?php
/*
 * Neue Seite in der Datenbank anlegen
 */
require ("../../../login/config_main.inc.php");


foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
}

$page_id = $_SESSION['smart_page_id'];
$page_lang = $_SESSION['page_lang'];
$set_site_id = $site_id;

// Speichert Verknuepfung zur Page und Profil
$sql = "REPLACE INTO smart_id_site2id_page SET
site_id   = '$site_id',
page_id   = '$page_id',
layout_id = '$profil_id',
menu_disable = '$menu_disable',
site_dynamic_id = '$site_dynamic_id'
";
$GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$site_id = mysqli_insert_id($GLOBALS['mysqli']);

// speichert die Allgemeine Daten der Webseite
$GLOBALS['mysqli']->query ( "REPLACE INTO smart_langSite SET
fk_id = '$site_id',
lang = '$page_lang',
title = '$site_title',
site_url = '$site_url',
menu_text = '$menu_text'
" ) or die ( mysqli_error ($GLOBALS['mysqli']) );

// Wenn noch keine Seite erzeugt worden ist, die diese in die smart_id_site2id_page eingetragen und positioniert
if (! $set_site_id) {
	if ($menu_position == 'end') {
		$query_max_position = $GLOBALS['mysqli']->query ( "SELECT MAX(position) from smart_id_site2id_page WHERE page_id = '$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		$array_max_position = mysqli_fetch_array ( $query_max_position );
		$new_position = $array_max_position[0] + 1;
		$parent_id = '';
	} else { // Postion und level auslesen
		$query = $GLOBALS['mysqli']->query ( "SELECT parent_id, position FROM smart_id_site2id_page WHERE site_id = '$menu_position' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		$array = mysqli_fetch_array ( $query );
		$new_position = $array['position'];
		$parend_id = $array['parent_id'];
		// Platz schaffen für Seite in Menue
		$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET position = position+1 WHERE site_id = '$menu_position' AND position > '$new_position' and page_id = '$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
	//Postion setzen
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET parent_id = '$parent_id', position = '$new_position' " );
}

echo"$(location).attr('href','index.php?site_select=$site_id');";
?>