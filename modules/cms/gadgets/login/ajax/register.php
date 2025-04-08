<?php
include ('../../config.php'); // call site_key & secret_key and more
include ('../function.php');
include ('../../../php_functions/functions.php'); 

// Verbindung zu Datenbank herstellen wenn Modul nicht von Facebook verwendet wird
foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );
}

// Email pruefen ob bereits in Datenbank vorhanden ist
$username_check_query = $GLOBALS['mysqli']->query ( "SELECT user_name FROM ssi_company.user2company WHERE user_name ='$email'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$username_check = mysqli_num_rows ( $username_check_query );
if ($username_check) {
	$error = 1;
	echo "$('#form_message2').html(\"<div class='ui negative tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>Username $email bereits vergeben</div>\");";
	return;
}

//Check ob Passwort stark genug ist
if ($err = pc_passwordcheck($email,$password_new)) {
	echo "$('#form_message2').html(\"<div class='ui negative tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>$err</div>\");";
	exit ();
	// Make the user pick another password
}

// Prüft ob sich ein Mensch angemeldet hat
$secretKey = $_SESSION['recaptcha']['secret_key'];
//$password_new = 'martin21';
$verifydata = file_get_contents ( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $_POST['recaptcha'] );
$response = json_decode ( $verifydata );
if ($response->success == false) {
	echo "$('#form_message2').html(\"<div class='ui negative tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>Bitte bestätigen, dass Sie kein Roboter sind!</div>\");";
	exit ();
}


// Parent User ID wird ausgelesen und in der Liste gespeichert
$query_verify = $GLOBALS['mysqli']->query ( "SELECT user_id FROM ssi_company.user2company WHERE verify_key = '{$_SESSION['parent_verify_key']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array_verify = mysqli_fetch_array ( $query_verify );
$parent_id = $array_verify['user_id'];
$_SESSION['parent_verify_key'] = ''; // Danach wird parent_verify_key wieder gelöscht
                                     
// verify_key erzeugen
$verify_key_new = md5 ( uniqid ( rand (), TRUE ) );

$password = md5 ( $password_new );

if ($email and $password) {
	// if ($firstname and $secondname and $email and $password) {
	
	// Zentrales Anlegen des User (erstellt eindeutige ID für den User)
	$GLOBALS['mysqli']->query ( "INSERT INTO ssi_company.user2company SET 
	company_id = $smart_company_id, 
	user_name = '$email',
	password  = '$password',
	parent_id = '$parent_id',
	verify_key = '$verify_key_new',
    smart_version = 'beta',
	right_id = '1',
	link = '$link',
	number_of_smartpage = '1'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) ); // if 1 then show just SMART-Kit
	
	$new_user_id = $_SESSION['user_id'] = mysqli_insert_id($GLOBALS['mysqli']);
	
	$GLOBALS['mysqli']->query ( "INSERT INTO $db_smart.module2id_user SET user_id = $new_user_id, module = 'smart' " );
	$GLOBALS['mysqli']->query ( "INSERT INTO $db_smart.module2id_user SET user_id = $new_user_id, module = 'newsletter' " );
	
	$_SESSION['verify_key'] = $verify_key_new;
	setcookie ( "verify_key", $_SESSION['verify_key'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );
	
	// Versendung der Verifizierung der Seite
	include ('../../../php_functions/function_sendmail.php');
	include ('../../config.php'); // Mail-config daten (Server,User,Passwort
	$sent_verification = send_verification ( 'verification', $MailConfig, $_SESSION['user_id'] );
	if ($sent_verification) $sent_verification = preg_replace( "/\r|\n/", "", $sent_verification );
	
	if ($sent_verification != 'ok') {
		echo "alert('Versendung der Verifizierung fehlgeschlagen: $sent_verification')";
		exit;
	}
	
	echo "$('#buttons_register').hide();";
	echo "$('#form_message2').html(\"<div class='ui positive tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>Anmeldung ist erfolgreich! Weiterleitung erfolgt... <i class='notched circle loading icon'></i></div>\");";
	if ($_GET['lp'] == 'center')
		echo "$(location).attr('href','../../../ssi_dashboard/');"; // lp = landing_page
	else
		echo "window.top.location.reload();";
	return;
} else {
	echo "$('#form_message2').html(\"<div class='ui negative tiny message'><i class='close icon'></i><div id='form_message_info' class='header'>Anmeldung ist fehlerhaft!</div>\");";
}
?>