<?php
include (__DIR__ . '/../f_config.php');

$GLOBALS['mysqli']->query("DELETE from bill_details WHERE bill_id = '{$_POST['bill_id']}'") or die(mysqli_error($GLOBALS['mysqli']));

?>