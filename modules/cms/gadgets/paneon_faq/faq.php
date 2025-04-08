<?php
//Get sendet from main.js
if ($_POST ['faq_id']) {
	include ('page.php');
	exit ();
}

$list_id = 'faq_list';
include ("../../smart_form/include_list.php");

$array = call_list ( 'array_faq.php', '../config.php' );
echo $array['html'];
echo $array['js'];

?>