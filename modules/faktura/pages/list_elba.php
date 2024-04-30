<?php
include (__DIR__ . '/../../../../smartform/include_list.php');

if ($_POST['list_filter']) {
	$_SESSION["filter"]['issues_list']['account_id'] = $_POST['list_filter'];
	$_SESSION["filter"]['issues_list']['SetYear'] = $_SESSION['SetYear'];
}

$array = call_list('../list/elba.php', '../f_config.php', );
echo $array['html'] . $array['js'];
echo "<script type=\"text/javascript\" src='js/list_elba.js'></script>";