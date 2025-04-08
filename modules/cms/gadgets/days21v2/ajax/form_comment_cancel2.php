<?php
/*
 * Kommentar abspeichern
 * mm@ssi.at am 06.01.2011
 */
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
}

$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET 
cancel_reasion = '$cancel_reasion',
result = '$comment', 
comment_better_way = '$comment_better_way'
WHERE challenge_id = '$challenge_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

echo "
$('#modal_challenge').modal('hide');
call_filter();
call_list();";
?>