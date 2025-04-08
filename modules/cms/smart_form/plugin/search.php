<?php
session_start();
include($_POST['config_path']);
include($_POST['mysql_connect_path']);

$q = $_GET['q'];

// MYSQL
$sql = $arr['mysql']['query']. " WHERE 1 ";

if ($arr['mysql']['where']) {
	$sql .= $arr['mysql']['where'];
}

if ($arr['mysql']['like']) {
	$search_field = true;
	//$sql .= " AND MATCH ({$arr['mysql']['like']}) AGAINST ('$q') ";
	$sql .= " AND (CONCAT({$arr['mysql']['like']}) LIKE '%$q%') ";
}

if ($arr['mysql']['inline_search']['limit'])
	$arr['mysql']['limit'] = $arr['mysql']['inline_search']['limit'];
	
if ($arr['mysql']['order'])
	$sql .= ' ORDER BY ' . $arr['mysql']['order'];
if ($arr['mysql']['limit'])
	$sql .= ' LIMIT ' . $arr['mysql']['limit'];

// echo $sql;

$return_arr = array();

$query = $GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $row = mysqli_fetch_array ( $query ) ) {
	
	$row_array['title'] = $row[$arr['mysql']['inline_search']['title']];
	$row_array['description'] = $row[$arr['mysql']['inline_search']['description']];
	
	array_push($return_arr,$row_array);
}

$arr = array(
		'success' => true,
		'results' => $return_arr
);

echo json_encode($arr);
