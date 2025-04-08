<?php

// Ruft site_key und secrect_key für recaptcher
include ('../config.php');

// Wenn $_POST['ajax'] gesetzt ist, wird 'gadgets/login/' in $add_link zugewiesen,
// andernfalls wird $add_link leer gelassen.
$add_link = isset ( $_POST ['ajax'] ) ? 'gadgets/login/' : '';

include ('../../php_functions/functions.php');

// Abrufen der Company-options
$option_array = call_company_option ( $smart_company_id, array ('register_allowed','facebook_login','img_logo' ) );

if ($option_array ['img_logo']) {
	$img_logo = "../../../company/$company_id/" . $option_array ['img_logo'];
}

if (! is_file ( $img_logo ))
	$img_logo = '../../../image/ssi_logo.png';

// Erweiterung für die Anmeldung über das Center (andere Darstellung)
if ($_GET ['lp'] == 'center') {
	// $html_add_open = "<div class='ui middle aligned center aligned grid'><div class='column'>";
	$html_add_open = "<br><br><div align=center>";
	$html_add_open .= "<img class=ssi_logo src='$img_logo' height='70'>";
	$html_add_open .= "<div class='ui segment' style='max-width:500px;'>";
	$html_add_close = "</div>";
	$html_add_close .= "</div>";
	$html_add_close .= "</div>";
	$css_ad = "<style type='text/css'>body { background-color: #EEE; } body > .grid { height: 100%; } .column { max-width: 550px; } </style>";
} // Aufruf bei internen Angelegenheiten wie Bazar und anderen Logins für das System
else {
	echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
}

include ('../../smart_form/include_form.php');
include ('facebook/config_facebook.php');

// Rechte erfragen
// $query = $GLOBALS['mysqli']->query ( "SELECT register_allowed FROM $db_smart.register" );
// $array = mysqli_fetch_array ( $query );
// $register_allowed = $array['register_allowed'];

// Wenn User bereits angemeldet ist wird weitergeleitet

// if (isset ( $_SESSION ['verify_key'] ) && isset ( $_SESSION ['userbar_id'] )) {
// 	echo "<div align=center>User bereits eingeloggt<br>UserID: {$_SESSION['user_id']}</div>";
// 	return;
// }


if (! isset ( $_POST ['ajax'] ) || ! $_POST ['ajax']) {
	?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<meta name='viewport'
	content='width=device-width, initial-scale=1.0, maximum-scale=1.0'>
<title>Login</title>
<script src="../../smart_form/jquery-ui/jquery.min.js"></script>
<script src="../../smart_form/semantic/dist/semantic.min.js"></script>
<?php
	if ($site_key) {
		echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
	}
	?>
<script src="main.js"></script>
<link rel="stylesheet" type="text/css" class="ui"
	href="../../smart_form/semantic/dist/semantic.min.css">
<? echo $css_ad; ?>
</head>
<body>
<?php
} else {
	echo "<script src='gadgets/login/main.js'></script>";
}
echo $html_add_open;

// if ($button_facebook_login) {
// echo "<br>";
// echo "<div align=center>$button_facebook_login<div class='ui horizontal divider'>oder</div></div>";
// echo $facebook_login_div;
// }

/*
 * STANDARD - LOGIN - MASKE
 */
$arr ['form'] = array ('id' => 'form_login','width' => '600','align' => 'center','action' => "$add_link" . "ajax/login.php?lp={$_GET['lp']}",'class' => 'big','inline' => 'list','keyboardShortcuts' => true );
$arr ['form'] ['jquery'] = array ('success' => "after_submit(), 'beforeSend' => '' " );
$arr ['field'] ['user'] = array ('value' => 'office@ssi.at','type' => 'input','placeholder' => 'Email','rules' => 'email','focus' => 'true','rules_save' => array ([ 'type' => 'email','prompt' => 'Email angeben' ] ) );
$arr ['field'] ['password'] = array ('type' => 'password','placeholder' => 'Passwort','rules' => 'empty','rules_save' => array ([ 'type' => 'empty','prompt' => 'Passwort eingeben' ] ) );
$arr ['field'] [] = array ('type' => 'content','text' => '<div id=form_message></div>' );
$arr ['buttons'] = array ('align' => 'center' );
$arr ['button'] ['submit'] = array ('value' => 'Einloggen','class' => 'big','color' => 'green','icon' => 'privacy' );

echo "<div class='ui grey header' align=center>Smart-Kit - Login</div>";
echo "<div id=div_form_login style='display:none'>";
$output = call_form ( $arr );
echo $output ['html'];
echo $output ['js'];

if ($option_array ['register_allowed'])
	echo "<br><div align=center><a href=# onclick=\"call_register()\" >[ Noch kein Konto? - Hier klicken ]</a></div>";

echo "</div>";

/*
 * REGISTRIER - MASKE
 */

$arr2 ['form'] = array ('id' => 'form_register','width' => '600','align' => 'center','action' => "$add_link" . "ajax/register.php?lp={$_GET['lp']}",'size' => 'big','class' => '','inline' => 'list','keyboardShortcuts' => true );

// $array_sex = array ( '' => '--Bitte w&auml;hlen--' , 'f' => 'Frau' , 'm' => 'Herr' , 'c' => 'Firma' );

// $arr2['field']['sex'] = array ( 'id' => 'sex' , 'label' => 'Anrede' , 'type' => 'dropdown' , 'array' => $array_sex, 'validate' => true, value=>'m', 'focus' => true );
// $arr2['field'][] = array ( 'type' => 'div' , 'class' => 'two fields' ); // 'label'=>'test'
// $arr2['field']['firstname'] = array ( 'id' => 'firstname' , 'label' => 'Vorname' , 'type' => 'input' , 'placeholder' => 'Vorname', 'validate' => true );
// $arr2['field']['secondname'] = array ( 'id' => 'secondname' , 'label' => 'Nachname' , 'type' => 'input' , 'placeholder' => 'Nachname', 'validate' => true );
// $arr2['field'][] = array ( 'type' => 'div_close' );

// $arr2['field'][] = array ( 'type' => 'div' , 'class' => 'fields' ); // 'label'=>'test'
// $arr2['field']['zip'] = array ( 'label' => 'Plz' , 'type' => 'input' , 'class' => 'four wide' , 'placeholder' => '1020' , rules => array ( [ 'type' => 'empty' , prompt => 'PLZ eingeben' ] , [ 'type' => 'integer' , prompt => 'Ungültige Eingabe' ] ) );
// $arr2['field']['city'] = array ( 'label' => 'Stadt' , 'type' => 'input' , 'placeholder' => 'Stadt','class' => 'six wide', 'validate' => true);
// $arr2['field']['country'] = array ( 'label' => 'Land' , 'type' => 'dropdown' , 'class' => 'six wide' , 'value' => 'at' , 'array' => 'country' );
// $arr2['field'][] = array ( 'type' => 'div_close' );
// $arr2['field']['street'] = array ( 'label' => 'Strasse' , 'type' => 'input' , 'placeholder' => 'Strasse');
$arr2 ['field'] ['email'] = array ('label' => 'Email','type' => 'input','placeholder' => 'email','validate' => 'email' );
$arr2 ['field'] ['password_new'] = array ('label' => 'Passwort','type' => 'smart_password','placeholder' => 'Passwort' );

if ($site_key) {
	$arr2 ['field'] [] = array ('type' => 'recaptcha','key' => $site_key ); // siehe config.php
}

$arr2 ['field'] [] = array ('type' => 'content','text' => '<div id=form_message2></div>' );
$arr2 ['buttons'] = array ('id' => 'buttons_register','align' => 'center' );
$arr2 ['button'] ['submit'] = array ('value' => 'Neuen Account anlegen','class' => 'big','icon' => 'add user','color' => 'green' );

echo "<div id=div_form_register style='display:none'>";
$output = call_form ( $arr2 );
echo $output ['html'];
echo $output ['js'];

echo "<br><div align=center><a href=# onclick=call_login() >[ Account schon vorhanden ]</a></div>";
echo "</div>";

echo $html_add_close;

if (! isset ( $_POST ['ajax'] )) {
	?>
	</body>
</html>
<?php } ?>