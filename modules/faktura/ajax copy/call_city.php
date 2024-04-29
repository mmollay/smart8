<?php
session_start();
include (__DIR__ . '/../f_config.php');

$zip = $_POST['zip'];

$sql_query = $GLOBALS['mysqli']->query("SELECT city FROM client WHERE company_id = '{$_SESSION['faktura_company_id']}' AND zip = $zip ") or die(mysqli_error($GLOBALS['mysqli']));
$array = mysqli_fetch_array($sql_query);
echo $array['city'];

?>