<?php
include (__DIR__ . '/../f_config.php');

$bill_id = $_POST['bill_id'];

// Prüfen welches Level
$query = $GLOBALS['mysqli']->query("SELECT remind_level FROM bills WHERE bill_id = $bill_id ") or die(mysqli_error($GLOBALS['mysqli']));
$fetch = mysqli_fetch_array($query);
if (!$fetch[0])
	$fetch[0] = 1;
$level = $fetch[0];
//Wenn $level = 4 dann bleibt der Level stand
if ($level == 4)
	$remind_level = 1;
else
	$remind_level = $level + 1;

// Auslesen der Mahnzeiten
$interval = mysql_singleoutput("SELECT remind_time$level FROM company WHERE company_id = '{$_SESSION['faktura_company_id']}' ");

// Default interavel
if (!$interval)
	$interval = 10;

$GLOBALS['mysqli']->query("UPDATE bills SET sendet = sendet+1, date_send = NOW(),
send_status = 'ok',
date_remind = DATE_ADD(NOW(), INTERVAL $interval DAY),
remind_level = '$remind_level'
WHERE bill_id = $bill_id ") or die(mysqli_error($GLOBALS['mysqli']));

logfile('Manuelle Setzung der Mahnung', "Die Mahnung wurde m&uuml;ndlich oder schriftlich &uuml;bermittelt ", 1, '', $bill_id);