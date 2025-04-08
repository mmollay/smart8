<?php
session_start ();

$id = $_POST['id'];

if ($id == 'map_search') {
	$_SESSION["map_filter"]['map_places'] = '';
}

/*
if ($id == 'map_zip' or $id == 'map_places') {
	$_SESSION["map_filter"]['map_search'] = '';
	echo "$('.search_input').attr('value',''); ";
}
*/

if ($id == 'map_zip') {
	$_SESSION["map_filter"]['map_places'] = '';
}

// Sortenfilter löschen
$_SESSION["map_filter_fruit"] = '';

//$_SESSION["map_filter"]['map_zip'] = '';