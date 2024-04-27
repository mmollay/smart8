<?php
session_start();
date_default_timezone_set('Europe/Berlin');

function get_time($time) {
	$duration = $time / 1000;
	$hours = floor($duration / 3600);
	$minutes = floor(($duration / 60) % 60);
	$seconds = $duration % 60;
	if ($hours != 0)
		return "$hours:$minutes:$seconds";
	else
		return "$minutes:$seconds";
}



$_SESSION['started'] = (!isset($_SESSION['started']) ? time() : $_SESSION['started']);

$count =  exec("ps -C apache2 | wc -l");
if ($count > $_SESSION['count']) $_SESSION['count'] = $count;
echo $count." (Größter Wert: ".$_SESSION['count'].")"; //" seit ".get_time($_SESSION['started']).")";

