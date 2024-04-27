<?php
/*
 * Martin Mollay martin@ssi.at am 14.03.2013
 * Setzt Status auf gedruckt -> versendet
 */
require ("../config.inc.php");

if ($_POST ['bill'] == 'all') {
	$sql_bill = $GLOBALS['mysqli']->query ( "
			SELECT * FROM bills
			WHERE remind_level = 0
			AND company_id = '{$_SESSION['faktura_company_id']}'
			AND date_booking = '0000-00-00'
			AND date_storno = '0000-00-00'
			AND (email = '' OR post = 1)
			" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	while ( $array = mysqli_fetch_array ( $sql_bill ) ) {
		// $bill_array[] = $array['bill_id'];
		save_print_status ( $array ['bill_id'] );
	}
} else {
	save_print_status ( $_POST ['bill'] );
}
function save_print_status($bill_id) {
	// Auslesen der Mahnzeiten
	$interval = mysql_singleoutput ( "SELECT remind_time1 FROM company WHERE company_id = '{$_SESSION['faktura_company_id']}' " );
	// $remind_level = mysql_singleoutput("SELECT remind_level FROM bills WHERE bill_id = $bill_id ");
	
	// if ($remind_level == '0') {
	// Default interavel
	if (! $interval)
		$interval = 10;
	$GLOBALS['mysqli']->query ( "UPDATE bills SET
	sendet = sendet+1,
	date_send = NOW(),
	send_status = 'ok',
	date_remind = DATE_ADD(NOW(), INTERVAL $interval DAY),
	remind_level = '1'
	WHERE bill_id = $bill_id " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	// }
	
	logfile ( "Rechnung ausgedruckt", "", $modul = 1, $bill_id );
}

?>