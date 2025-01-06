<?
session_start ();
require ("../config.inc.php");
$GLOBALS['mysqli']->query ( "UPDATE todo SET finished_date = NOW() WHERE todo_id = '{$_POST['id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
?>