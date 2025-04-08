<?php
include (__DIR__ . '/../config.php');
date_default_timezone_set ( 'Europe/Vienna' );
include ('config_map.php');
include ('function.inc.php');

if ($_POST['map_zip'])
	$_SESSION["map_filter"]['map_zip'] = $_POST['map_zip'];

$cfg_mysql['db'] = 'ssi_faktura';

$GLOBALS['mysqli']->select_db ( $cfg_mysql['db'] ) or die ( 'Could not select database ' . $cfg_mysql['db'] );

if ($_SESSION["map_filter"]['map_search'] == '')
	$_SESSION["map_filter"]['map_search'] = '';

// if (! isset ( $_SESSION["map_filter"]['autofit'] )) {
// $_SESSION["map_filter"]['autofit'] = 1;
// }

// Call default-destination from db
// if ($destination && ! $_SESSION["map_filter"]['map_zip']) {
// $_SESSION["map_filter"]['map_zip'] = $destination;
// }

$map_zip = $_SESSION["map_filter"]['map_zip'];

if ($map_zip == 'all') 
	$map_places = $_SESSION["map_filter"]['map_places'] =  'all';

$map_places = $_SESSION["map_filter"]['map_places'];
$map_search = $_SESSION["map_filter"]['map_search'];
$map_not_defined = $_SESSION["map_filter"]['not_defined'];
$map_set_admin = $_SESSION["map_filter"]['set_admin'];

if ($map_zip == 'all') {} elseif ($map_zip) {
	$add_mysql .= " AND tree.zip = '$map_zip' ";
}

if ($map_places == 'all') {} else if ($map_places) {
	$add_mysql .= " AND tree.district2 = '$map_places' ";
}

// if ($map_not_defined) {
// 	$add_mysql .= " OR (tree.client_faktura_id = 0 AND tree.trash=0 )";
// } else {
// 	$add_mysql .= "AND search_sponsor = 0 ";
// }

// searchtext
if ($map_search) {
	// $add_mysql .= " AND MATCH(client.company_1,client.firstname,client.secondname,client.web,latin,tree_group_lang.title,tree_panel) AGAINST ('{$map_search}*' IN BOOLEAN MODE)";
	$add_mysql .= " AND MATCH(client.company_1,client.firstname,client.secondname,client.web,latin,tree_panel,kind) AGAINST ('{$map_search}*' IN BOOLEAN MODE)";
}
