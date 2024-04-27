<?php
// mm@ssi.at am 16.07.2017
include (__DIR__ . '/../f_config.php');

$client_id = $GLOBALS['mysqli']->real_escape_string($_POST['client_id']);

$query = $GLOBALS['mysqli']->query("SELECT * FROM client WHERE client_id = '$client_id' ") or die(mysqli_error($GLOBALS['mysqli']));
$array = mysqli_fetch_array($query);

foreach ($array as $key => $value) {
	$value = str_replace("\n", "\\n", $value); // brauchte es wenn Zeilenumbrüche verwendet werden	

	if (in_array($key, array("country", "gender"))) {
		echo "$('#dropdown_$key').dropdown('set selected','$value');";
	} else {
		echo "$('#$key').val('$value');";
	}

}
