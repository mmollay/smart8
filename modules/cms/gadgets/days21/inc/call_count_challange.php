<?
//Dieser Counter wird im Dashboard angezeigt

// Verbindung zur Datenbank herstellen
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

$date = DATE ( 'Y-m-d' );
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_groups, $db_smart.21_sessions WHERE challenge_id = group_id AND user_id = '$userbar_id'  AND action_date <= '$date' AND action = '' AND !failed_date" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$count = mysqli_num_rows ( $query );

if ($count)
	$day_info = "<div style='position:relative; left:50px; top:10px;' class='floating ui red label'>$count</div>";
?>