<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../function.php');

include_once ('../check_login.php');

$user_id = $_SESSION['userbar_id'];
$id = $GLOBALS['mysqli']->real_escape_string($_POST['id']);

$comment = $_POST['comment'];
$comment = chop ( $comment );
$comment = trim($comment);
$comment =  preg_replace('/(\r?\n){3,}/', '$1$1', $comment);

$comment_mysql = $GLOBALS['mysqli']->real_escape_string($comment);

$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_comment SET comment = '$comment_mysql' WHERE comment_id = $id AND user_id = '$user_id' " ) or die (mysqli_error());

echo nl2br($comment);
