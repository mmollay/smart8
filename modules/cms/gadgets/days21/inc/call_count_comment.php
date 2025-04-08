<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include_once ('../function.php');
$element = $_POST['element'];
$id = $_POST['id'];

// Anzahl der Einträge ermitteln
$count = comment_button_count ( $id, $element );

echo "$('#{$id}.comment_button_count_{$element}').replaceWith(\" $count\")";
?>