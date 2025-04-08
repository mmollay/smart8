<?php
include_once ('../config.inc.php');

if (isset ( $_POST['group_default_id'] ))
	$_SESSION['group_default_id'] = $_POST['group_default_id'];
if (isset ( $_POST['group_id'] ))
	$_SESSION['group_id'] = $_POST['group_id'];
	// For the backlink for Paypal
if (isset ( $_POST['url_name'] ))
	$_SESSION['url_name'] = $_POST['url_name'];
	
	/*
 * Call PAYPAL-STATUS "SUCCESS"
 * - Generate Bill und release Article for the client
 * - Putout Message "Paypal was uccessful"
 * - Cancel SESSION "paypal_success"
 */

$msg_back_button = "<br><br><a href='{$_SESSION['baseaddress']}?group_id=$group_id' class='button_small'>$strBackToShop</a>";

if ($_SESSION['paypal_success']) {
	$msg_Paypal = $strSuccessPayedWithPaypal; // "cart/call_main.php"
	$msg_Paypal .= $msg_back_button;
	$_SESSION['paypal_success'] = '';
	$group_id = $_SESSION['group_id'] = $_SESSION['group_default_id'] = $_SESSION['back_group_id'];
	$_SESSION['back_group_id'] = '';
} /*
   * Call PAYPAL-STATUS "CANCEL"
   * - Putout Message
   * - Cancel SESSION "paypal_cancel"
   */
elseif ($_SESSION['paypal_cancel']) {
	$msg_Paypal = "<font color=red>$strCancelPaypalTransaction</font>"; // Siehe "cart/call_main.php"
	$msg_Paypal .= $msg_back_button;
	$_SESSION['paypal_cancel'] = '';
	$group_id = $_SESSION['group_id'] = $_SESSION['group_default_id'] = $_SESSION['back_group_id'];
	$_SESSION['back_group_id'] = '';
}
$_SESSION['back_group_id'] = '';

// $msg_Paypal .= $msg_back_button.$_SESSION['back_group_id'];

// $strButtonHelp = "<a href=# id='button_help'>$strButtonHelp</a> | ";
$strButtonHelp = '';
// If user is logged in, View menu bar

$check_query = $GLOBALS['mysqli']->query ( "SELECT hp_inside, activ, abo,company_id FROM client WHERE client_id = '{$_SESSION['client_user_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

$check_array = mysqli_fetch_array ( $check_query );
$_SESSION['hp_inside'] = $check_array['hp_inside'];
$_SESSION['activ'] = $check_array['activ']; // User kann auf allen Artikel zugreifen und downloaden (WTM und OEGT)
$_SESSION['abo'] = $check_array['abo'];
$_SESSION['client_company_id'] = $check_array['company_id'];

if ($check_array['hp_inside'] != 1)
	$right = "<span id='account_inactive' style='color:red' title='$strStatusInactiveHelp'>$strStatusInactive</span> | ";
	
	/*
 * IF User ist logged-in
 */
if ($_SESSION['client_user_id']) {
	
	if (! $_SESSION['oegt_user'] and ! $_SESSION['abo']) {
		// $left .= "<button class=button_small id=button_back_to_shop>$strBackToShop</button>";
		$left .= "<button class='button ui basic small icon' id=button_my_products>$strMyProducts</button>";
	}
	$right .= $strButtonHelp;
	$right .= "<span id=panal_right>";
	$right .= "<b>{$_SESSION['client_username']}</b> ";
	if ($show['button_registration']) $right .= "<button class='button ui basic small icon' id=button_setting_account>$strMySettings</button>";
	$right .= "</span>";
	$right .= "<a href=# class='button ui basic small icon' id='button_logout_account'>$strButtonLogout</a>";
}  /*
   * No User logged-in
   */
else {
	$left .= "";
	$right = "<button class='button ui basic small icon' onclick='button_login_account()'><i class='icon key'></i>$strButtonLogin</button>";
 	if ($show['button_registration']) $right .= "<button  class='button ui basic small icon' onclick='button_reg_account()'>$strButtonReg</button>";
}

include_once ('cart/call_cart.php');
include_once ('cart/call_main.php'); // $content
$content .= "<link rel=stylesheet type='text/css' href='gadgets/portal/cart/shop.css'>";
$content .= "<script type='text/javascript' src='gadgets/portal/cart/shop.js'></script>";

$content .= "<div class=version_line>© 2016 <a href=http://www.ssi.at target='_parend'>SSI</a>
(v $portal_version, Company-ID:$company_id)</div>";

// Call ArticleList
// $right .= "";

// }

if ($_POST['load_main']) {
	// $user_bar .= "<script type='text/javascript' src='gadgets/portal/js/main.js'></script>";
	$user_bar .= "<link rel=stylesheet type='text/css' href='gadgets/portal/css/font.css'>";
}

// if ($_SESSION['client_user_id'])
$user_bar .= "<div class=panal><span style='float:left'>$left</span><span style='float:right'>$right</span><div style='clear:both'></div></div>";
// else $user_bar .= $right;

$user_bar .= "<div class=content>$content</div>";

?>