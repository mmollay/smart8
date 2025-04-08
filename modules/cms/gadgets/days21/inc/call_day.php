<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
$challenge_id = $_POST['challenge_id'];

$query = $GLOBALS['mysqli']->query ( "SELECT COUNT(*) FROM $db_smart.21_sessions WHERE group_id = '$challenge_id' AND action='success' " );
$array = mysqli_fetch_array ($query);
echo $array[0];
?>