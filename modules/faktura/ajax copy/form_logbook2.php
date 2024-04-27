<?php
/*
 * Auslesen aktueller Positionen
 */
require ("../config.inc.php");

//Auslesen der CLient_id
$client_id = mysql_singleoutput("SELECT client_id FROM bills WHERE bill_id = '{$_POST ['bill_id']}'");

logfile ( 'Manueller Eintrag', $_POST ['message1'], 1, $client_id, $_POST ['bill_id'] );

echo "Eintrag ins Logbuch ist erfolgt";
?>