<?php
/*
 * Save Formular
 * mm@ssi.at 12.07.2011
 * update: 25.05.2017 - call_gadget_layer now globals call
 */
include ('../config.php');
$layer_id = $_POST['layer_id'];
if (! $layer_id)
	echo "Versendung nicht möglich es fehlt die LayerID";

require_once ('../function.inc.php');
// Config auslesen
call_layer_parameter ( $layer_id );
$array['user_name'] = $receive_email; // Alternative Emailempfänger auslesen
$array['set_recaptcha'] = $recaptcha; // Check ob Recaptcha-Function

/**
 * *********************************************************
 * Prüft ob Anmeldung über Pot läuft oder einem Menschen
 * *********************************************************
 */
if ($array['set_recaptcha']) {
	$secretKey = $secret_key;
	$verifydata = file_get_contents ( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $_POST['recaptcha'] );
	$response = json_decode ( $verifydata );
	if ($response->success == false) {
		echo "Bitte bestätigen, dass Sie kein Roboter sind!";
		exit ();
	}
}
/**
 * *********************************************************
 */

// call IP - from Client
if (! isset ( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
	$client_ip = $_SERVER['REMOTE_ADDR'];
} else {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$firstname = $GLOBALS['mysqli']->real_escape_string ( $_POST['firstname'] );
$secondname = $GLOBALS['mysqli']->real_escape_string ( $_POST['secondname'] );
$email_to = $GLOBALS['mysqli']->real_escape_string ( $_POST['email'] );
$group_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['group_id'] );
$intro = $GLOBALS['mysqli']->real_escape_string ( $_POST['intro'] );
$message = $GLOBALS['mysqli']->real_escape_string ( $_POST['message'] );
$telefon = $GLOBALS['mysqli']->real_escape_string ( $_POST['telefon'] );
$send_domain = $_SERVER["HTTP_HOST"]. " -> " .$site_id;

$layer_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['layer_id'] );
$from_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['from_id'] );

$_POST['message'] = nl2br ( $_POST['message'] );

$GLOBALS['mysqli']->query ( "INSERT into $db_smart.smart_feedback_traffic SET
	user_id = '$user_id',
	firstname = '$firstname',
	secondname = '$secondname',
	email  = '$email_to',
	telefon = '$telefon',
	message = '$message',
	client_ip = '$client_ip',
	send_domain = '$send_domain'	
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$contact_id = mysqli_insert_id ( $GLOBALS['mysqli'] );

// Uebergabe von Vor und Nachname falls vorhanden
if ($firstname or $secondname or $email_to) {
	$body_message .= "<i>Achtung! - Bitte als Antwortadresse nicht den Absender der Email sondern $email_to verwenden.</i><br><br>";
	$body_message .= "Name: $firstname $secondname<br>";
	$body_message .= "Email: $email_to<br>";
	if ($telefon)
		$body_message .= "Telefon: $telefon<br><br>";
	$body_message .= $_POST['message'];
}

// $email_from = "office@ssi.at";
$subject = "Emailnachricht von " . $send_domain;

/**
 * *****************************************************************
 * Config - Emails
 * *****************************************************************
 */

if (! $array['user_name']) {
	// Email-Empfänger
	$query = $GLOBALS['mysqli']->query ( "SELECT user_name FROM ssi_company.user2company WHERE user_id = '$user_id' " );
	$array = mysqli_fetch_array ( $query );
}

// Versendet Email
$message = send_mail ( $from_id, $array['user_name'], $subject, $body_message );
if ($message == 'ok')
	echo 'ok';
else
	echo $message;
?>