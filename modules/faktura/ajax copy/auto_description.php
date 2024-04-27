<?php
/*
 * Auslesen von bestehenden Beschreibungen
 * UPdate am 10.07.2013 -> Call company_1 for Issues
 */
require ("../config.inc.php");

$term = $_GET ['term'];

// Company_1
if ($_GET ['get'] == 'company_1') {
	
	$query = $GLOBALS['mysqli']->query ( "SELECT company_1 FROM issues WHERE  company_1 LIKE '%$term%' GROUP by company_1 " ) or die ( mysqli_error ($GLOBALS['mysqli']) ); // company_id = '{$_SESSION['faktura_company_id']}' AND
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$array_out [] = $array [0];
	}
} // Description
else {
	$query = $GLOBALS['mysqli']->query ( "SELECT description FROM issues WHERE  description LIKE '%$term%' GROUP by description " ) or die ( mysqli_error ($GLOBALS['mysqli']) ); // company_id = '{$_SESSION['faktura_company_id']}' AND
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$array_out [] = $array [0];
	}
}
if (! $array_out)
	$array_out = '';

echo json_encode ( $array_out );

?>