<?
session_start();
include (__DIR__ . '/../f_config.php');
$GLOBALS['mysqli']->query("UPDATE todo SET finished_date = NOW() WHERE todo_id = '{$_POST['id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
?>