<?php
/*
 * Auslesen aktueller Positionen
 */
require ("../config.inc.php");

/*
 * Bei AUSGABEN
 */
if ($_POST ['issue_id']) {
	// Template anlegen
	$GLOBALS['mysqli']->query ( "UPDATE issues SET
	date_booking = '',
	date_storno = ''
	WHERE bill_id = '{$_POST['issue_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo mysqli_insert_id ($GLOBALS['mysqli']);
} /*
 * Bei EINNAHMEN
 */
else {
	$booking_total = nr_format2english ( $_POST ['booking_total'] );
	// Template anlegen
	$GLOBALS['mysqli']->query ( "UPDATE bills SET
	date_booking = '',
	date_storno = '',
	booking_total = '',
	booking_command = ''
	WHERE bill_id = '{$_POST['bill_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo mysqli_insert_id ($GLOBALS['mysqli']);
}
?>