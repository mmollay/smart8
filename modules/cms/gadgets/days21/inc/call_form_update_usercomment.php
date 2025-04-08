<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../check_login.php');

$id = $_POST['id'];

$query = $GLOBALS['mysqli']->query("SELECT comment FROM $db_smart.21_comment WHERE comment_id = '$id' and user_id = '$userbar_id' ") or die(mysqli_error());
$array = mysqli_fetch_array($query);
$comment = $array['comment'];

$comment = chop($array['comment']);
$comment = trim($comment);
$comment = preg_replace('/(\r?\n){3,}/', '$1$1', $comment);

$button_comment_text .= "<div class='field'><textarea placeholder='Schreibe dein Kommentar' class='textarea_comment' id='$id' rows='2'>$comment</textarea></div>";
$button_comment_text .= "<div class=buttons_comment>";
$button_comment_text .= "<button class='ui button mini green'  onclick='submit_update_comment($id)' >Änderung speichern</button>";
$button_comment_text .= "<button class='ui button mini silver break_comment' onclick='cancel_update_comment($id)' >Abbrechen</button>";
$button_comment_text .= "</div>";

// echo $button_comment_text;
echo $button_comment_text;