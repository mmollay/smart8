<?php
//localhost/smart7/ssi_service/manuel/oegt_new_nr2021.php
include_once ('../../login/config_main.inc.php');

$bill_number = '20210001';
$first_bill_id = '17501'; //Start bei dieser Rechnungsnummer

$query = $GLOBALS ['mysqli']->query ( "SELECT * from ssi_faktura94.bills WHERE bill_id >= '$first_bill_id' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$bill_id = $array['bill_id'];
	$GLOBALS ['mysqli']->query ( "UPDATE ssi_faktura94.bills SET bill_number = '$bill_number' WHERE bill_id = '$bill_id' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$bill_number ++;
}

echo "fertig mit Veränderung von $bill_number Rechnungen (Nummerauf)"; 