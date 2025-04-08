<?
// Verbindung zur Datenbank herstellen
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

// Superuser kann Challenge löschen sonst niemand
//if ($superuser) {
	$del_id = $_POST['challenge_id'];
	$GLOBALS['mysqli']->query ( "DELETE from $db_smart.21_groups WHERE challenge_id = '$del_id'  " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$GLOBALS['mysqli']->query ( "DELETE from $db_smart.21_sessions WHERE group_id = $del_id " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$GLOBALS['mysqli']->query ( "DELETE from $db_smart.21_comment WHERE challenge_id = '$del_id'  " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$GLOBALS['mysqli']->query ( "DELETE from $db_smart.21_like WHERE challenge_id = '$del_id'  " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo 'ok';
//}
?>