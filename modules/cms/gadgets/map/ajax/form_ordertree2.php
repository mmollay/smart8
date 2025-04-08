<?php
require ("../mysql_map.inc.php");

// Zugangsdaten fuer die Datenbank
foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
}

// Client -Number (eindeutig) wird ausgelesen +1
$query = $GLOBALS['mysqli']->query ( "SELECT MAX(client_number) FROM client" );
$array = mysqli_fetch_array ( $query );
$client_number = $array[0] + 1;

// Neuer Client wird eingetragen
$GLOBALS['mysqli']->query ( "INSERT INTO client SET
			user_id = '$user_id',
			client_number = '$client_number',
			reg_date = now(),
			map_page_id = '{$_SESSION ['smart_page_id']}',
			map_user_id = '{$_SESSION['user_id']}',
			company_1 = '$company_1',
			company_2 = '$company_2',
			firstname = '$firstname',
			secondname = '$secondname',
			web   = '$web',
			email = '$email',
			tel  = '$tel',
			`desc` = '$desc',
			gender = '$gender',
			commend = '$commend',
			zip = '$zip',
			city = '$city',
			country = '$country',
			street = '$street',
			logo   = '$logo' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

$client_id = mysqli_insert_id ( $GLOBALS['mysqli'] );

// Save and UPDATE new Tree
$GLOBALS['mysqli']->query ( "
			UPDATE tree SET
			search_sponsor = '0',
			baum_pate  = '$baum_pate',
			plant_id = '$plant_id',
			tree_panel = '$tree_panel',
			sponsor_progress = '1',
			client_id = '$client_id',
			client_faktura_id = '$client_id'
			WHERE tree_id = '$tree_id'
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

echo "ok";
?>