<?php
if ($_POST ['ajax']) {
	include ('../config.inc.php');
}

if ($_POST ['id']) {
	$_SESSION ['cart'] [$_POST ['id']] = $_POST ['id'];
}

// Remove Article
if ($_POST ['del_id']) {
	unset ( $_SESSION ['cart'] [$_POST ['del_id']] );
}

if ($_SESSION ['cart']) {
	foreach ( $_SESSION ['cart'] as $cart_id ) {
		if ($cart_id) {
			$query = $GLOBALS['mysqli']->query ( "SELECT art_title, tax, netto FROM article_temp LEFT JOIN accounts ON account_id = account  WHERE temp_id='$cart_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
			$array = mysqli_fetch_array ( $query );
			$netto = number_format ( $array ['netto'], 2, ",", "." );
			$brutto = number_format ( $array ['netto'] / 100 * ($array ['tax'] + 100), 2, ",", "." );
			$cart_list .= "<tr><td align=left>" . $array ['art_title'] . $array ['tax'] . "</td><td align=right nowrap>" . $brutto . " €</td><td align=right width=35><button title='Entfernen' onclick=del_article($cart_id) class='ui icon button mini'><i class='icon remove'></i></button></td></tr>";
		}
	}
}
if (! $cart_list)
	$cart_list = "<br><div align=center>$strCartTextEmpty</div><br>";
else
	$cart_list = "<input type=hidden id=article_in_cart value=1><table border=0 cellpadding=1 cellspacing=0 width=100% >$cart_list</table>";

if ($_SESSION ['client_user_id'] and $_SESSION ['cart'])
	$button_order = "<br><div align=center><button id=button_call_cart onclick='call_order()' class='ui button small' >$strShopButtonOrder</button></div>";
	// else { $button_order = "<button class=button_small id='button_login_account2'>$strButtonLogin</button><button class=button_small id='button_reg_account2'>$strButtonReg</button>";}
	
/*
 * CART
 */

$content_cart = "$cart_list" . $button_order;

if ($_POST ['ajax']) {
	echo "<div class=cart_dialog>$content_cart</div>";
}