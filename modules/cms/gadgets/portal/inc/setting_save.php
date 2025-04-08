<?php
include ("../config.inc.php");

$company_1 = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['company_1'] ) );
$firstname = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['firstname'] ) );
$secondname = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['secondname'] ) );
$email = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['email'] ) );
$password_new = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['password'] ) );
$zip = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['zip'] ) );
$country = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['country'] ) );
$street = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['street'] ) );
$city = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['city'] ) );
$gender = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['gender'] ) );
$mobil = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['mobil'] ) );
$newsletter = trim ( $GLOBALS['mysqli']->real_escape_string ( $_POST ['newsletter'] ) );

if ($password_new != $_SESSION ['client_password']) {
	$_SESSION ['client_password'] = $password_new;
	// If Set Cookie change as well for cookie
	if ($_COOKIE ["client_password"]) {
		setcookie ( "client_password", $_SESSION ['client_password'], time () + 3600 * 24 * 30 );
	}
}

// number = '$number',
$GLOBALS['mysqli']->query ( "UPDATE client SET
	company_1    = '$company_1',
	password     = '$password_new',
	gender       = '$gender',
	firstname    = '$firstname',
	mobil        = '$mobil',
	zip          = '$zip',
	street       = '$street',
	city         = '$city',
	secondname   = '$secondname',
	country      = '$country',
	newsletter   = '$newsletter'
	WHERE client_id = '{$_SESSION['client_user_id']}' LIMIT 1
" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
echo "ok";
?>