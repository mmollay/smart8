<?php
/*
 * Auslesen aktueller Positionen
 */
require ("../config.inc.php");
if (! $_POST['bill_id']) {
	echo "error";
	exit ();
}

foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
	//$GLOBALS[$key] = $value;
}

/*
 * Bei AUSGABEN
 */
if ($_POST['issue_id']) {
	// Template anlegen
	$GLOBALS['mysqli']->query ( "UPDATE issues SET
	date_booking = '$date_booking',
	WHERE bill_id = '$issue_id'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	echo mysqli_insert_id ( $GLOBALS['mysqli'] );
} /*
   * Bei EINNAHMEN
   */
else {
	$booking_total = nr_format2english ( $_POST['booking_total'] );
	// Template anlegen
	$GLOBALS['mysqli']->query ( "UPDATE bills SET
	date_booking = '$date_booking',
	booking_total = '$booking_total',
	booking_command = '$booking_command'
	WHERE bill_id = '$bill_id'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	// echo mysqli_insert_id($GLOBALS['mysqli']);
	echo $bill_id;
}
?>