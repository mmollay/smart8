<?php
include (__DIR__ . '/../f_config.php');

foreach ($_POST as $key => $value) {
	if ($value) {
		$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
	}
}


$GLOBALS['mysqli']->query("DELETE FROM data_elba WHERE elba_id = '$elba_id' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));

echo "$('body').toast({message: 'Ein Eintrag wurde entfernt'});";
echo "$('#tr_$elba_id').remove();";