<?php
include (__DIR__ . '/../../../../smartform/include_list.php');

if ($_POST['list_filter']) {
	$_SESSION["filter"]['issues_list']['account_id'] = $_POST['list_filter'];
	$_SESSION["filter"]['issues_list']['SetYear'] = 'DATE_FORMAT(date_create,"%Y") = "' . $_SESSION['SetYear_finance'] . '"';
}
$array = call_list('../list/issues.php', '../f_config.php');
echo $array['html'] . $array['js'];
