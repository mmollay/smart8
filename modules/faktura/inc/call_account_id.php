<?php
include (__DIR__ . '/../f_config.php');
$search_text1 = $GLOBALS['mysqli']->real_escape_string($_POST['search_text1']);
$search_text2 = $GLOBALS['mysqli']->real_escape_string($_POST['search_text2']);

if ($search_text1)
	$query = $GLOBALS['mysqli']->query("SELECT account FROM issues WHERE ( description = '$search_text1') ") or die(mysqli_error($GLOBALS['mysqli']));

if ($search_text2)
	$query = $GLOBALS['mysqli']->query("SELECT account FROM issues WHERE ( description = '$search_text2') ") or die(mysqli_error($GLOBALS['mysqli']));

$array = mysqli_fetch_array($query);

echo $array['account'];
?>