<?php
/*
 * Auslesen aktueller Positionen
 */
include (__DIR__ . '/../f_config.php');

// Template anlegen
/*
 * $GLOBALS['mysqli']->query("INSERT INTO logbook SET
 * message = '{$_POST['message']}',
 * bill_id = '{$_POST['bill_id']}',
 * remote_ip = '{$_SERVER['REMOTE_ADDR']}'
 * ") or die (mysqli_error());
 */

logfile('Manueller Eintrag', $_POST['message1'], 1, '', $_POST['bill_id']);

echo "Eintrag ins Logbuch ist erfolgt";
?>