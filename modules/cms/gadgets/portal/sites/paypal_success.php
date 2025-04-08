<?php
/*
 * Weiterleitung nach erfolgreicher Bezahlung mit Paypal
 */
/*
 * Token for "gec.php
 */
$GLOBALS ['token'] = $_REQUEST ['token'];
include ('../paypal/gec.php');

/*
 * Pfad
 */
$dirname = dirname ( $_SERVER ['REQUEST_URI'] );

// remove path for direct path to the base
$dirname = preg_replace ( "/gadgets\/portal\/sites/", "", $dirname );
/*
 * Basis-Index-Name für die Shopseite (Bsp.: shop.html)
 */
// echo $_SESSION['baseaddress'];

/*
 * Domain der Seite auslesen
 */
// echo $_SERVER["HTTP_HOST"];

// $_SESSION['url_name'] wird am Anfang erzeugt und über Session weiter gereicht
$url = "http://" . $_SERVER ['HTTP_HOST'] . $dirname . $_SESSION ['url_name'];
$_SESSION ['paypal_success'] = true;

include ('../config.inc.php');
// Call Rechnung erstellen und verbuchen
$paypal_add_mysql = "
	booking_command = 'paypal',
	booking_total   = '$paymentAmount',
	date_booking    = NOW(),";
include ('../cart/call_order3.php');

echo $url;

Header ( "Location: $url" );
exit ();