<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../function.php');

include_once ('../check_login.php');

$id = $_POST['id'];
$element = $_POST['element'];
$user_id = $_SESSION['userbar_id'];

// Prüft Comment Challenge Eintrag
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_like WHERE element_id = '$id' AND element = '$element' AND user_id ='$user_id'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$count = mysqli_num_rows ( $query );

if (!$count) {
	$GLOBALS['mysqli']->query ( "INSERT INTO $db_smart.21_like SET 
	user_id = '$user_id', 
	element_id = '$id',
	element = '$element',
	challenge_id = '{$_SESSION['challenge_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
} else {
	//Wenn Beitrag nicht mehr gefällt
	$GLOBALS['mysqli']->query ( "DELETE FROM $db_smart.21_like 
	WHERE user_id = '$user_id' 
	AND element_id = '$id'
	AND element = '$element' " );
	
}

$count = like_button_count ( $id, $element );
echo "$('.tooltip').popup('hide');";
echo "$('#{$id}.like_button_count_{$element}').html(\"$count\");";
echo "$('.tooltip').popup();";