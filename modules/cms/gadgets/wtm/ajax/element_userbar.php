<?php
include ('../mysql.php');

$strStatusInactive = 'Inaktiv';
$strStatusInactiveHelp = 'Konto muss noch per Email bestätigt werden. (ev. bitte auch Spamordner prüfen)';

$check_query = $GLOBALS['mysqli']->query ( "SELECT hp_inside, activ, abo,company_id FROM client WHERE client_id = '{$_SESSION['client_user_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$check_array = mysqli_fetch_array ( $check_query );
$_SESSION['hp_inside'] = $check_array['hp_inside'];
$_SESSION['activ'] = $check_array['activ']; // User kann auf allen Artikel zugreifen und downloaden (WTM und OEGT)
$_SESSION['abo'] = $check_array['abo'];
$_SESSION['client_company_id'] = $check_array['company_id'];

if ($check_array['hp_inside'] != 1)
	$right = "<span id='account_inactive' style='color:red' title='$strStatusInactiveHelp'>$strStatusInactive</span> | ";

if ($_SESSION['client_user_id']) {
	
	// if (! $_SESSION['oegt_user'] and ! $_SESSION['abo']) {
	// $left .= "<button class='button ui small icon' onclick=call_my_articles() >Meine Artikel</button>";
	// }
	$right .= $strButtonHelp;
	$right .= "<span id=panal_right>";
	$right .= "<b>{$_SESSION['client_username']}</b> ";
	//if ($show['button_registration']) $right .= "<button class='button ui small icon' id=button_setting_account>Einstellungen</button>";
	$right .= "</span>";
	$right .= "<a href=# class='button ui small icon' onclick=button_logout_account()>Ausloggen</a>";
}  /*
   * No User logged-in
   */
else {
	$left .= "";
	$right = "<button class='button ui red small icon' onclick='button_login_account()'><i class='icon key'></i> Log In</button>";
	//if ($show['button_registration']) $right .= "<button  class='button ui small icon' onclick='button_reg_account()'>Registrieren</button>";
}

if (! $_POST['reload']) {
	echo "<div id='portal_login_bar' style='visibility:hidden; display:none; position:absolute; right:15px; top:15px;'>";
}

echo "$left $right";

if (! $_POST['reload']) {
	echo "</div>";
}