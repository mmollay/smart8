<?php
// Config for the Shopmodul
include (__DIR__ . '/../config.php');
include (__DIR__ . '/lang/de.inc.php');

$portal_version = '2.4';

/*
 * MYSQL - Config
 */
if ($user_id == '40')
	$cfg_mysql['db'] = "ssi_faktura";
else
	$cfg_mysql['db'] = "ssi_faktura$user_id";

$cfg_mysql['and'] = "and company_id = $company_id";

mysqli_select_db ( $GLOBALS['mysqli'], $cfg_mysql['db'] ) or die ( 'Could not select database' . $cfg_mysql['db'] );

// $show['button_registration'] = true;
// $show['cart'] = true;
/*
 * Mail-Config
 */
/**
 * **************************************************************
 * Default - for all other companys
 * **************************************************************
 */
// include ('../config.inc.php');

$setMailConfig['default']['to_mail'] = 'martin@ssi.at';
$setMailConfig['default']['bestellung'] = $MailConfig['from_email'];
$setMailConfig['default']['from_email'] = $MailConfig['from_email'];
$setMailConfig['default']['from_title'] = $MailConfig['from_title'];
$setMailConfig['default']['return_path'] = $MailConfig['return_path'];
$setMailConfig['default']['smtp_host'] = $MailConfig['smtp_host'];
$setMailConfig['default']['smtp_user'] = $MailConfig['smtp_user'];
$setMailConfig['default']['smtp_password'] = $MailConfig['smtp_password'];
$setMailConfig['default']['smtp_port'] = $MailConfig['smtp_port'];
$setMailConfig['default']['smtp_secure'] = $MailConfig['smtp_secure'];

// Paypal - Config
$setPaypalConfig['default']['API_UserName'] = 'office_1327347467_biz_api1.ssi.at';
$setPaypalConfig['default']['API_Password'] = 'L7WP5SU2V4HPZL2D';
$setPaypalConfig['default']['API_Signature'] = 'AFcWxV21C7fd0v3bYYYRCpSSRl31AaDP7f.pnHnjj4CRnGWE3dQedRHv';
$setPaypalConfig['default']['environment'] = 'sandbox'; // or live

/**
 * **************************************************************
 * Bestandsbetreuung company_id = 1080
 * **************************************************************
 */

$setMailConfig['1080']['to_mail'] = 'bestandsbetreuung.wdk@vetmeduni.ac.at';
$setMailConfig['1080']['from_email'] = 'response@ssi.at';
$setMailConfig['1080']['bestellung'] = 'bestandsbetreuung.wdk@vetmeduni.ac.at';
$setMailConfig['1080']['from_title'] = 'Bestellung Bestandsbetreuung';
$setMailConfig['1080']['return_path'] = 'bestandsbetreuung.wdk@vetmeduni.ac.at';

// $setMailConfig['1080']['smtp_host'] = $MailConfig['smtp_host'];
// $setMailConfig['1080']['smtp_user'] = $MailConfig['smtp_user'];
// $setMailConfig['1080']['smtp_password'] = $MailConfig['smtp_password'];
// $setMailConfig['1080']['smtp_port'] = $MailConfig['smtp_port'];
// $setMailConfig['1080']['smtp_secure'] = $MailConfig['smtp_secure'];
/*
 * $setMailConfig['1080']['smtp_host'] = 'smtps.vetmeduni.ac.at';
 * $setMailConfig['1080']['smtp_user'] = '\\vmudomain\bestandsbetreuungwdk';
 * $setMailConfig['1080']['smtp_password'] = 'donaufeld11';
 * $setMailConfig['1080']['smtp_port'] = '587';
 * $setMailConfig['1080']['smtp_secure'] = 'tls';
 * *
 *
 * /***************************************************************
 * WTM und OEGT company_id = 31
 * **************************************************************
 */
$setMailConfig['31']['to_mail'] = 'registrierung@wtm.at';
$setMailConfig['31']['from_email'] = 'registrierung@wtm.at';
$setMailConfig['31']['bestellung'] = 'office@wtm.at'; // oegt@vetmeduni.ac.at';
$setMailConfig['31']['from_title'] = "WTM";
$setMailConfig['31']['return_path'] = 'registierung@wtm.at';
// $setMailConfig['31']['bestellung'] = 'martin@ssi.at';
$setMailConfig['31']['smtp_host'] = 'smtp.gmail.com';
$setMailConfig['31']['smtp_user'] = 'registrierung@wtm.at';
$setMailConfig['31']['smtp_password'] = 'wtmwtm21;';
$setMailConfig['31']['smtp_port'] = '465';
$setMailConfig['31']['smtp_secure'] = 'ssl';
$TrackingCode['31'] = 'UA-67518411-2';

// Paypal - Config
$setPaypalConfig['31']['API_UserName'] = 'oegt_api1.vetmeduni.ac.at';
$setPaypalConfig['31']['API_Password'] = '77Y7DV6NU7RUVVE2';
$setPaypalConfig['31']['API_Signature'] = 'AOSUDyDb-ONw4QIYgUPZ7v5Q0fWlAfSk.ugZooJNqbRr155nERS8trrv';
$setPaypalConfig['31']['environment'] = 'live'; // or live
                                                
// Paypal - Config (Bei Aktivierung - auskommentieren)
                                                // $setPaypalConfig['31']['API_UserName'] = 'office_1327347467_biz_api1.ssi.at';
                                                // $setPaypalConfig['31']['API_Password'] = 'L7WP5SU2V4HPZL2D';
                                                // $setPaypalConfig['31']['API_Signature'] = 'AFcWxV21C7fd0v3bYYYRCpSSRl31AaDP7f.pnHnjj4CRnGWE3dQedRHv';
                                                // $setPaypalConfig['31']['environment'] = 'sandbox'; //or live

/**
 * ***************************************************************
 * END of CONFIG
 * ***************************************************************
 */

$MailConfig['controll_mail'] = $setMailConfig[$company_id]['to_mail'];
$MailConfig['bestellung'] = $setMailConfig[$company_id]['bestellung'];
$MailConfig['from_email'] = $setMailConfig[$company_id]['from_email'];
$MailConfig['to_mail'] = $setMailConfig[$company_id]['to_mail'];
$MailConfig['return_path'] = $setMailConfig[$company_id]['return_path'];
$MailConfig['from_title'] = $setMailConfig[$company_id]['from_title'];
$MailConfig['smtp_host'] = $setMailConfig[$company_id]['smtp_host'];
$MailConfig['smtp_user'] = $setMailConfig[$company_id]['smtp_user'];
$MailConfig['smtp_password'] = $setMailConfig[$company_id]['smtp_password'];
$MailConfig['smtp_port'] = $setMailConfig[$company_id]['smtp_port'];
$MailConfig['smtp_secure'] = $setMailConfig[$company_id]['smtp_secure'];

$GLOBALS['API_UserName'] = urlencode ( $setPaypalConfig[$company_id]['API_UserName'] );
$GLOBALS['API_Password'] = urlencode ( $setPaypalConfig[$company_id]['API_Password'] );
$GLOBALS['API_Signature'] = urlencode ( $setPaypalConfig[$company_id]['API_Signature'] );
$GLOBALS['environment'] = $setPaypalConfig[$company_id]['environment']; // or live

if (! $setMailConfig[$company_id]['to_mail'])
	$MailConfig['controll_mail'] = $setMailConfig['default']['to_mail'];
if (! $setMailConfig[$company_id]['bestellung'])
	$MailConfig['bestellung'] = $setMailConfig['default']['bestellung'];
if (! $setMailConfig[$company_id]['from_email'])
	$MailConfig['from_email'] = $setMailConfig['default']['from_email'];
if (! $setMailConfig[$company_id]['return_path'])
	$MailConfig['return_path'] = $setMailConfig['default']['return_path'];
if (! $setMailConfig[$company_id]['from_title'])
	$MailConfig['from_title'] = $setMailConfig['default']['from_title'];
if (! $setMailConfig[$company_id]['smtp_host'])
	$MailConfig['smtp_host'] = $setMailConfig['default']['smtp_host'];
if (! $setMailConfig[$company_id]['smtp_user'])
	$MailConfig['smtp_user'] = $setMailConfig['default']['smtp_user'];
if (! $setMailConfig[$company_id]['smtp_password'])
	$MailConfig['smtp_password'] = $setMailConfig['default']['smtp_password'];
if (! $setMailConfig[$company_id]['smtp_port'])
	$MailConfig['smtp_port'] = $setMailConfig['default']['smtp_port'];
if (! $setMailConfig[$company_id]['smtp_secure'])
	$MailConfig['smtp_secure'] = $setMailConfig['default']['smtp_secure'];

if ($_POST['group_id'])
	$_SESSION['group_id'] = $_POST['group_id'];
$group_id = $_SESSION['group_id'];

$default_path_intern = "../../../$default_path_extern";
$relative_path = "gadgets/portal/";

// call IP - from Client
if (! isset ( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
	$client_ip = $_SERVER['REMOTE_ADDR'];
} else {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

/*
 * Special vars for Bestandsbetreuung
 */

if ($company_id == '1080') {
	include ("../lang/{$_SESSION['lang']}_93.inc.php");
	$show['button_registration'] = true;
	$show['cart'] = true;
} else {
	$show['button_registration'] = false;
	$show['cart'] = false;
}

include_once ('../function.php');

if ($_COOKIE["portal_username"])
	$_SESSION['portal_username'] = $_COOKIE["portal_username"];
if ($_COOKIE["portal_password"])
	$_SESSION['portal_password'] = $_COOKIE["portal_password"];

if ($_SESSION['portal_username'] && $_SESSION['portal_password']) {
	// Check whether user is valid
	$check_query = $GLOBALS['mysqli']->query ( "SELECT cliend_id as id FROM client WHERE email = '{$_SESSION['portal_username']}' and password = '{$_SESSION['portal_password']}' and company_id = '$company_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$check_array = mysqli_fetch_array ( $check_query );
	$_SESSION['portal_user_id'] = $check_array['id'];
} else
	
	$_SESSION['portal_user_id'] = "";

//Logfile for the User
/*
 if ($_SESSION['portal_user_id']) {
 $path = $_SERVER['PHP_SELF'];
 $file = basename($path);
 $GLOBALS['mysqli']->query("INSERT INTO log_user SET portal_user_id = '{$_SESSION['portal_user_id']}', ip = '{$client_ip}', page = '$file' ") or die (mysqli_error());
 }

 if ($set_TrackingCode and $TrackingCode and !$_SESSION['admin_modus']) {
 include_once('../trackingCode.inc.php');
 echo $add_analytics_js;
 }
 */