<?php 
//Get sendet from main.js
if ($_POST ['report_id']) {
	include ('page.php');
	exit ();
}

$list_id = 'report_list';
include ("../../smart_form/include_list.php");
$array = call_list ( 'array_report.php', '../config.php' );
echo $array['html'];
echo $array['js'];
