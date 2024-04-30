<?php
include (__DIR__ . '/../f_config.php');

foreach ($_POST as $key => $value) {
	if ($value) {
		$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
	}
}

if ($automator_id) {


	// check inner DB
	$sql = "SELECT  word FROM automator where automator_id = '$automator_id' ";
	$query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query);
	$word = $array['word'];
	//$word = str_replace("\n", "<br>", $array['word']);
	$word = $GLOBALS['mysqli']->real_escape_string($word);

	echo "$('body').toast({message: 'Wort-Liste geladen'});";
	echo "$('#word').val('$word');";
	echo "$('#word').focus();";
}