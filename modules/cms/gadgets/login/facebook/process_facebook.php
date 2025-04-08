<?php
/**
 * *******************************************************************************
 * Abrufen der User-Daten von FACEBOOK
 * *******************************************************************************
 */
// Speichern in der db
include ('../../config.php');
include_once ("config_facebook.php"); // Include configuration file.
require_once ('inc/facebook.php'); // include fb sdk

/* Detect HTTP_X_REQUESTED_WITH header sent by all recent browsers that support AJAX requests. */
if (! empty ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest') {

	// initialize facebook sdk
	$facebook = new Facebook ( array ('appId' => $appId,'secret' => $appSecret,'cookie' => true ) );

	$fbuser = $facebook->getUser ();

	if ($fbuser) {
		try {
			// Proceed knowing you have a logged in user who's authenticated.
			$me = $facebook->api ( '/me?fields=email,first_name,last_name,gender,link,locale,website,birthday' ); // user
			$uid = $facebook->getUser ();
		} catch ( FacebookApiException $e ) {
			// echo error_log($e);
			$fbuser = null;
		}
	}

	// redirect user to facebook login page if empty data or fresh login requires
	if (! $fbuser) {
		$loginUrl = $facebook->getLoginUrl ( array ('redirect_uri' => $return_url,false ) );
		// header ( 'Location: ' . $loginUrl );
	} else {

		// user details
		$array ['email'] = $me ['email'];
		$array ['first_name'] = $me ['first_name'];
		$array ['last_name'] = $me ['last_name'];
		$array ['gender'] = $me ['gender'];
		$array ['link'] = $me ['link'];
		$array ['locale'] = $me ['locale'];
		$array ['location'] = $me ['location'];
		$array ['website'] = $me ['website'];
		$array ['birthday'] = $me ['birthday'];
		$array ['uid'] = $uid;

		// user details
		echo check_fbuser_db ( $array );
	}
}

// Prüft ob FB-User in Datenbank ist
function check_fbuser_db($arr) {
	global $db_smart;
	$query = $GLOBALS ['mysqli']->query ( "SELECT user_id FROM ssi_company.user2company WHERE fbid='{$arr['uid']}' and user_name = '{$arr['email']}'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $query );
	if ($array ['user_id']) {
		return set_login ( $array ['user_id'] );
	} else {
		// User wird registriert
		return reg_user ( $arr );
	}
}

// User in Datenbank schreiben
function reg_user($array) {
	global $db_smart;
	global $smart_company_id;

	// Check user exists
	$query = $GLOBALS ['mysqli']->query ( "SELECT user_id FROM ssi_company.user2company WHERE user_name = '{$array['email']}'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$check_array = mysqli_fetch_array ( $query );

	// Wenn User bereits angemeldet wurde aber mit FB angemeldet wird
	if ($check_array ['user_id']) {
		$GLOBALS ['mysqli']->query ( "UPDATE ssi_company.user2company SET fbid = '{$array['uid']}', gender = '{$array['gender']}',link = '{$array['link']}' WHERE user_id ='{$check_array['user_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		// User komplett neu anlegen
		return set_login ( $check_array ['user_id'] );
	} else {

		$verify_key_new = md5 ( uniqid ( rand (), TRUE ) );
		$GLOBALS ['mysqli']->query ( "INSERT INTO ssi_company.user2company SET
			company_id = '$smart_company_id'
			firstname   = '{$array['first_name']}',
			secondname  = '{$array['last_name']}',
			user_name = '{$array['email']}',
			verify_key = '$verify_key_new',
			right_id = '1',
			fbid = '{$array['uid']}',
			gender = '{$array['gender']}',
			link = '{$array['link']}',
			number_of_smartpage = '1'
			" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) ); // if 1 then show just SMART-Kit

		// smart Modul freigeschalten
		$GLOBALS ['mysqli']->query ( "INSERT INTO $db_smart.module2id_user SET user_id = $new_user_id, module = 'smart' " );
		return set_login ( $user_id );
	}
}

// Session ID - Setzen zum eingeloggt bleiben
function set_login($user_id) {
	$_SESSION ['user_id'] = $user_id;
	setcookie ( "user_id", $_SESSION ['user_id'], time () + 60 * 60 * 24 * 365, '/', $_SERVER ['HTTP_HOST'] );
	return 'ok';
}

?>