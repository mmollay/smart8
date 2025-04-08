<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../function.php');
include_once ('../check_login.php');

$id = $GLOBALS['mysqli']->real_escape_string($_POST['id']);

//Call element + challenge_id
$query = $GLOBALS['mysqli']->query("SELECT element,challenge_id FROM $db_smart.21_comment WHERE comment_id = $id ") or die (mysqli_error());
$array = mysqli_fetch_array($query);

$challenge_id = $array['challenge_id'];
$element = $array['element'];

$GLOBALS['mysqli']->query("DELETE FROM $db_smart.21_comment WHERE comment_id = '$id' AND user_id = '$userbar_id' ") or die (mysqli_error());

$count = comment_button_count ( $challenge_id, $element );

echo "$('#{$challenge_id}.comment_button_count_{$element}').replaceWith(\"$count\");";
echo "$('#{$_POST['id']}.container_comment_div').remove();";