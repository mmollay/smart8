<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../check_login.php');

$id = $_POST['id'];

$query = $GLOBALS['mysqli']->query ("SELECT comment FROM $db_smart.21_comment WHERE comment_id = '$id' and user_id = '$userbar_id' ") or die (mysqli_error());
$array = mysqli_fetch_array($query);
$comment = $array['comment']; 
echo nl2br($comment);