<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');

include_once ('../check_login.php');

$id = $_POST['id'];
$area = $_POST['element'];

$button_comment_text .= "<div class=\'ui form\'><div class=\'field\'><textarea placeholder=\'Schreibe dein Kommentar\' id=\'$id\' rows=\'3\'></textarea></div>";
$button_comment_text .= "<div class=buttons_comment>";
$button_comment_text .= "<button class=\'ui button mini green\'  onclick=\'submit_comment($id,\"$area\")\' >Kommentar posten</button>";
$button_comment_text .= "<button class=\'ui button mini silver break_comment\' onclick=\'cancel_comment($id,\"$area\")\' >Abbrechen</button>";
$button_comment_text .= "</div>";

//echo $button_comment_text;
echo "$('.container_form_usercomment_{$area}_{$id}').html('$button_comment_text');";
echo "$('#$id.textarea_comment').autosize().focus();";