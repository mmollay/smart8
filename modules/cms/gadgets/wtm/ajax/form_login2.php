<?php
include ('../mysql.php');

// auslesen der Company ID
$sql = "SELECT company_id FROM client WHERE email='{$_POST['client_username']}' and password='{$_POST['client_password']}' ";
$result = $GLOBALS['mysqli']->query ( $sql );
$array_company_id = mysqli_fetch_array ( $result );
$company_id = $array_company_id [0];

// Spezieller Abruf fuer oegt und WTM
if ($company_id == '31' or $company_id == '30') {
	$sql = "SELECT email,password,client_id as id,company_id FROM client WHERE email='{$_POST['client_username']}' and password='{$_POST['client_password']}' ";
} else {
	// now validating the username and password
	$sql = "SELECT email,password,client_id as id,company_id FROM client WHERE email='{$_POST['client_username']}' and password='{$_POST['client_password']}' and company_id= '$company_id' ";
}

$result = $GLOBALS['mysqli']->query ( $sql );
$row = mysqli_fetch_array ( $result );
// if username exists
if (mysqli_num_rows ( $result ) > 0) {
	$_SESSION ['client_username'] = $row ['email'];
	$_SESSION ['client_password'] = $row ['password'];
	$_SESSION ['client_user_id'] = $row ['id'];
	$_SESSION ['client_company_id'] = $row ['company_id'];
	// if ($row['company_id'] == '30') $_SESSION['oegt_user'] = $row['company_id'];
	// Cookies merken wenn vorhanden
	if ($_POST ['bazar_set_cookie']) {
		setcookie ( "client_username", $_SESSION ['client_username'], time () + 3600 * 24 * 30 );
		setcookie ( "client_password", $_SESSION ['client_password'], time () + 3600 * 24 * 30 );
		setcookie ( "cliente_user_id", $_SESSION ['client_user_id'], time () + 3600 * 24 * 30 );
		setcookie ( "client_company_id", $_SESSION ['client_company_id'], time () + 3600 * 24 * 30 );
		// if ($row['oegt_user'] == '30') setcookie("oegt_user", $_SESSION['oegt_user'], time()+3600*24*30);
	} else {
		setcookie ( "client_username", "", time () - 3600 );
		setcookie ( "client_password", "", time () - 3600 );
		setcookie ( "client_user_id", "", time () - 3600 );
		setcookie ( "client_company_id", "", time () - 3600 );
		setcookie ( "oegt_user", "", time () - 3600 );
	}
	echo 'ok';
} else {
	echo 'error';
}