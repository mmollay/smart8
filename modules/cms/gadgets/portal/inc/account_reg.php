<?php
include ("../config.inc.php");

// include ("../config.inc.php");

$company_1 = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['company_1'] ) );
$firstname = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['firstname'] ) );
$secondname = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['secondname'] ) );
$email = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['email'] ) );
$password_new = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['password_new'] ) );
$street = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['street'] ) );
$zip = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['zip'] ) );
$mobil = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['mobil'] ) );
$country = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['country'] ) );
$city = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['city'] ) );
$gender = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['gender'] ) );
$newsletter = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST['newsletter'] ) );

$reg_domain = $_SERVER["HTTP_HOST"];
$verify_key = md5 ( uniqid ( rand (), TRUE ) );
// Check User Email
$check_email_query = $GLOBALS['mysqli']->query ( "SELECT * FROM client WHERE email = '$email' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
if (mysqli_num_rows ( $check_email_query )) {
	echo "exist";
	exit ();
}

/*
 * 1.INSERT NEW ACCOUNT
 * 2.SEND USER AN CONVERMATION
 */

// Auslesen der letzten client_number
$client_number_query = $GLOBALS['mysqli']->query ( "SELECT MAX(client_number) from client where 1 and company_id = '$company_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$client_number_array = mysqli_fetch_array ( $client_number_query );
$client_number = $client_number_array[0] + 1;

$GLOBALS['mysqli']->query ( "INSERT INTO client SET
	email        = '$email',
	client_number= '$client_number',
	company_id   = '$company_id',
	password     = '$password_new',
	gender       = '$gender',
	firstname    = '$firstname',
	company_1    = '$company_1',
	street       = '$street',
	mobil        = '$mobil',
	newsletter   = '$newsletter',
	zip          = '$zip',
	city         = '$city',
	secondname   = '$secondname',
	country      = '$country',
	reg_ip       = '$client_ip',
	reg_date     = now(),
	reg_domain   = '$reg_domain',
	verify_key    = '$verify_key'
" ) or die ( mysqli_error ($GLOBALS['mysqli']) );

// Call new ID
$id = mysqli_insert_id($GLOBALS['mysqli']);

// Set User and Password for Login
$_SESSION['client_username'] = $email;
$_SESSION['client_password'] = $password_new;
$_SESSION['client_user_id'] = $id;

// LogIn permantently
if ($_POST['persistent_cookie']) {
	setcookie ( "client_username", $_SESSION['client_username'], time () + 3600 * 24 * 30 );
	setcookie ( "client_password", $_SESSION['client_password'], time () + 3600 * 24 * 30 );
	setcookie ( "client_user_id", $_SESSION['client_user_id'], time () + 3600 * 24 * 30 );
}

require_once ('../../function.inc.php');

/**
 * *****************************************************************
 * SEND CONFERMATION
 * Config - Emails
 * *****************************************************************
 */
$subject = preg_replace ( "/{domain}/", "$reg_domain", $strMailSubjectConfirm ); // Replace "Domainname"
$destination = "http://$reg_domain/$relative_path" . "sites/confirm.php?verify_key=$verify_key";
$message = "$strMailTextConfirm\n\n$destination";
$MailConfig['to_email'] = $email;
$MailConfig['subject'] = $subject;
$MailConfig['message'] = $message;
send_mail ( '', $MailConfig['to_email'], $MailConfig['subject'], $MailConfig['message'] );
/**
 * ******************************************************************
 * Send Confermationmail to the headquarter
 * Wurde deaktiviert, es gibt momentan keinen controlluser
 * ******************************************************************
 */
// $MailConfig['to_email'] = $MailConfig['controll_mail'];
// $MailConfig['subject'] = "Neue Anmeldung auf der Webseite: " . $_SERVER["HTTP_HOST"];
// $MailConfig['message'] = "
// Name: $firstname $secondname
// Email: $email";
// send_mail ( $MailConfig['from_email'], $MailConfig['to_email'], $MailConfig['subject'], $MailConfig['message'] );

/**
 * ******************************************************************
 * Kontrollmail an mich
 * ******************************************************************
 */
$MailConfig['to_email'] = 'martin@ssi.at';
$MailConfig['subject'] = "Kontrolle Anmeldung ->" . $_SERVER["HTTP_HOST"];
$MailConfig['message'] = "
Kontrolluser: {$MailConfig['controll_mail']}
Name: $firstname $secondname
Email: $email";
send_mail ('', $MailConfig['to_email'], $MailConfig['subject'], $MailConfig['message'] );

echo "ok";