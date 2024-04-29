<?php
include (__DIR__ . '/../f_config.php');

$GLOBALS['mysqli']->query("UPDATE bills SET date_storno = NOW() WHERE bill_id = '{$_POST['bill_id']}' LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));

?>