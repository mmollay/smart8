<?php
session_start ();
error_reporting(0);

//$_SESSION['smart_page_id'] = '';

if (!isset( $_SESSION['user_id'])) {
	$error_message = "ID-User ist nicht definiert!<br>";
	$set_error = true;
	// exit ();
	
	//Ruft bei Ajax-Reload eine Weiterleitung auf
	if ($_POST['ajax']) echo $error_message;
}

if (! $_SESSION['smart_page_id']) {
	$error_id = 1;
	$error_message .= "ID-Page ist nicht definiert!<br>";
	$set_error = true;
	
	//Ruft bei Ajax-Reload eine Weiterleitung auf
	if ($_POST['ajax']) echo $error_message;
	// exit ();
}

if ($set_error) {
	$error_message .= "Seite bitte neu laden...";
}

$_SESSION['page_lang'] = 'de';

$user_id = $_SESSION['user_id'];
$page_id = $_SESSION['smart_page_id'];
$site_key = $_SESSION['site_key'];
$secret_key = $_SESSION['secret_key'];

// Der reale Pfad auch wenn im APACHE ein Alias vorhanden ist, der zulässt, dass man unter www.xxx.at/admin in das System kommet
$PATH_ABSOLUTE_SMARTKIT = preg_replace ( "[/ssi_smart]", '', (__DIR__) );

$path_id_user = $_SESSION['path_id_user'] = $PATH_ABSOLUTE_SMARTKIT . $_SESSION['path_user'] . "user$user_id/";
$path_id_explorer_folder = "$path_id_user" . "explorer/" . $page_id . '/';


//$path_id_explorer_folder = dirname(dirname(dirname ( dirname ( __DIR__ ) )))."/smart_users/ssi/user$user_id/explorer/$page_id";;


// Für die Darstellung der Gallerie
$_SESSION['PATH_RELATIVE_EXPLORER'] = $_SESSION['path_user'] . "user$user_id/explorer/$page_id";


date_default_timezone_set ( 'Europe/Berlin' );

$_SESSION['load_js'] = array();