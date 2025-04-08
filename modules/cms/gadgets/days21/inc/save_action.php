<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
$challenge_id = $_POST['challenge_id'];
$action = $_POST['action'];
$date = date ( 'Y-m-d' );
// echo "ok";
// exit;
//Anzahl der geleisteten Challenges berrechnen
$query = $GLOBALS['mysqli']->query ( "SELECT action FROM $db_smart.21_sessions WHERE group_id = '$challenge_id' AND action='success'" )  or die ( mysqli_error ($GLOBALS['mysqli']) );;
$count = mysqli_num_rows ( $query );

//Falls der letzte Tag nach Ablauf bestätigt wird
if ($count == '20') {
	$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_sessions SET action ='$action' WHERE nr=21 AND group_id = '$challenge_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$count++;
}
else 
	$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_sessions SET action ='$action' WHERE action_date='$date' AND group_id = '$challenge_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

if ($action == 'fail') {
	$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET failed_date =NOW(), status = 'fail' WHERE challenge_id = '$challenge_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
}

if ($action == 'success') {
	//Am 21sten Tag wird die Datenbank auf erfolgreich gesetzt
	if ($count == '21') {
		$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET success_date =NOW(), status = 'success' WHERE challenge_id = $challenge_id" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
}

echo "ok";
?>