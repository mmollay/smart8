<?php
include ("../../ssi_smart/smart_form/include_list.php");

if ($_POST['list_filter']) {
	$_SESSION["filter"]['issues_list']['account_id'] = $_POST['list_filter'];
	$_SESSION["filter"]['issues_list']['SetYear'] = 'DATE_FORMAT(date_create,"%Y") = "'.$_SESSION['SetYear_finance'].'"';
}
$array = call_list ( '../list/issues.php', '../config.inc.php' );
echo $array['html'] . $array['js'];
