<?php
/*
 * Auslesen aktueller Positionen
 */
include (__DIR__ . '/../f_config.php');

//Auslesen der CLient_id
$client_id = mysql_singleoutput("SELECT client_id FROM bills WHERE bill_id = '{$_POST['bill_id']}'");

logfile('Manueller Eintrag', $_POST['message1'], 1, $client_id, $_POST['bill_id']);

echo "Eintrag ins Logbuch ist erfolgt";
?>