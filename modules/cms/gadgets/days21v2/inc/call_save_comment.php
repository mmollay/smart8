<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../function.php');

include_once ('../check_login.php');

$id = $_POST ['id'];
$user_id = $_SESSION ['user_id'];
$element = $_POST ['element'];
$comment = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['comment'] );

$GLOBALS ['mysqli']->query ( "INSERT INTO $db_smart.21_comment SET 
element_id = '$id',
element = '$element',
user_id = '$user_id', 
challenge_id = '{$_SESSION['challenge_id']}',
comment = '$comment' 
" );

$comment_id = mysqli_insert_id ( $GLOBALS ['mysqli'] );

$query = $GLOBALS ['mysqli']->query ( "SELECT firstname, secondname from ssi_company.user2company WHERE user_id = '$user_id' " );
$array = mysqli_fetch_array ( $query );

//Parameter fuer die Ausgabe des neuen User-Comment
$array2 = array ('comment_id' => $comment_id,'user_id' => $_SESSION ['user_id'],'comment' => $_POST ['comment'],'timestamp' => 'date' ( 'd.m.Y' ),'firstname' => $array ['firstname'],'secondname' => $array ['secondname'] );

$comment_list = user_comment_detail ( $array2 );

echo $comment_list;
