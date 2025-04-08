<?php
if ($_POST ['ajax']) {
	session_start ();
	include ('../config.inc.php');
}

if (! $_SESSION ['client_user_id']) {
	$content_cart = $strCartButNoLogtin;
	return;
}

if ($_SESSION ['cart']) {
	foreach ( $_SESSION ['cart'] as $cart_id ) {
		if ($cart_id) {
			$query = $GLOBALS['mysqli']->query ( "SELECT art_title, tax, netto FROM article_temp LEFT JOIN accounts ON account_id = account  WHERE temp_id='$cart_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
			$array = mysqli_fetch_array ( $query );
			$netto = number_format ( $array ['netto'], 2, ",", "." );
			$array ['brutto'] = $array ['netto'] / 100 * ($array ['tax'] + 100);
			$brutto = number_format ( $array ['netto'] / 100 * ($array ['tax'] + 100), 2, ",", "." );
			$cart_list .= "<tr><td align=left>" . $array ['art_title'] . "</td><td  align=right>" . $brutto . "</td></tr>"; // <button onclick=del_article($cart_id) class=del_article>X</button>";
			$sum += $array ['brutto'];
		}
	}
	$_SESSION ['summery'] = $sum;
	$sum = number_format ( $sum, 2, ",", "." );
	$cart_list .= "<tr><td align=left class=cart_line>" . $strCartSumery . "</td><td align=right class=cart_line><b>" . $sum . "</b></td></tr>"; // <button onclick=del_article($cart_id) class=del_article>X</button>";
}

if ($cart_list)
	$cart_list = "<table align=center border=0 width=60% cellpadding=5 cellspacing=0 id=content_cart>$cart_list</table>";
else
	$cart_list = "<br>$strCartTextEmpty<br><br>";

if ($company_id == 31)
	$set_paypal = true;
	/*
 * CART
 */
$content_cart = "" . "<h1 class=h1_shop>$strCartTitleCart2</h1>$cart_list<hr>" . "<div align=center>
$strShopTextBeforeSend" . "<br><br>" . "<button class='button ui small' onclick=cart_close()>$strShopButtonBack</button><br>
</div>";

if ($set_paypal) {
	$content_cart .= "<h1 class=h1_shop>Variante 1</h1>";
	$content_cart .= "
	<div align=center>
	Artikel sofort downloadbar.<br><br>
	<button class='button ui small' onclick=submit_order_paypal()>$strShopButtonSendPaypal</button>
	</div>";
	$content_cart .= "<h1 class=h1_shop>Variante 2</h1>";
} else
	$content_cart .= "<h1 class=h1_shop>Bezahlen</h1>";

$content_cart .= "<div align=center>
Artikel downloadbar erst nach Zahlungseingang.<br><br>
<button class='button ui small' onclick=submit_order()>$strShopButtonSend</button></div>";

// $content_cart .="</div>";

if ($_POST ['ajax']) {
	echo $content_cart;
}