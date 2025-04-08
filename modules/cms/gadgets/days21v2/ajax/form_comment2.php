<?php
/*
 * Kommentar abspeichern
 * mm@ssi.at am 04.03.2015
 */
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
}

$date = date('Y-m-d');
$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_sessions SET 
comment = '$comment',
difficulty = '$difficulty'
WHERE action_date='$date' AND group_id = $challenge_id " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

echo "
$('#modal_challenge').modal('hide');
call_filter();
call_list();";
?>