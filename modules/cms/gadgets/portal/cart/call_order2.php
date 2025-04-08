<?php
if ($_POST ['ajax']) {
	session_start ();
	include ('../config.inc.php');
}

if ($_POST ['paypal'] == true) {
	// echo "Bezahlen mit Paypal";
	/*
	 * call paypal routine
	 */
	$_SESSION ['back_group_id'] = $_SESSION ['group_default_id'];
	include ("../paypal/sec.php");
	return;
}

// finishing the progress
include ("call_order3.php");