<?
if (! $GLOBALS['set_counter_digit']) {
	$add_path_js .= "<script type='text/javascript' src='gadgets/clock/jquery.MyDigitClock.js'></script>";
}
$set_counter_digit = $GLOBALS['set_counter_digit'] ++;

$add_js2 .= "$(document).ready(function (){ $('#div_digit$set_counter_digit').MyDigitClock(); });\n";

$output .= "<span id='div_digit$set_counter_digit'></span>";