<?php
//Speichert manuell, weil die Rechung per Post versendet wurde

include (__DIR__ . '/../f_config.php');

$bill_id = $_POST['bill_id'];

// Prüfen welches Level

$query = $GLOBALS['mysqli']->query("SELECT remind_level FROM bills WHERE bill_id = '$bill_id' ") or die(mysqli_error($GLOBALS['mysqli']));
$fetch = mysqli_fetch_array($query);

$remind_level = $fetch[0];

if ($remind_level > 1) {
	// Setzt den Reminder zurück
	$GLOBALS['mysqli']->query("UPDATE bills SET 
	remind_level = remind_level-1, 
	date_remind = now()
	WHERE bill_id = $bill_id ") or die(mysqli_error($GLOBALS['mysqli']));
}