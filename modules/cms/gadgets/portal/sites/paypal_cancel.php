<?php
/*
 * Weiterleitung nach erfolgreicher Bezahlung mit Paypal
 * mm@ssi.at am 27.01.2012
 */
session_start ();

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

$url = "http://" . $_SERVER ['HTTP_HOST'] . $dirname . $_SESSION ['url_name'];
$_SESSION ['paypal_cancel'] = true;

// echo $url;

Header ( "Location: $url" );
exit (); 
