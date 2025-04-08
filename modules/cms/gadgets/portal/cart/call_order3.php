<?
if ($_SESSION ['cart'] and $_SESSION ['client_user_id'] and $_SESSION ['client_company_id']) {
	
	foreach ( $_SESSION ['cart'] as $cart_id ) {
		// Call Article (Temp)
		$query = $GLOBALS['mysqli']->query ( "SELECT * FROM article_temp,accounts where account = account_id and temp_id='$cart_id'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		$array [$cart_id] = mysqli_fetch_array ( $query );
		$tax = $array [$cart_id] ['tax'];
		$netto = $array [$cart_id] ['netto'];
		$count = $array [$cart_id] ['count'];
		$brutto = $netto / 100 * $tax + $netto;
		$netto_sum += $netto * $count;
		$brutto_sum += $brutto * $count;
	}
	
	$query1 = $GLOBALS['mysqli']->query ( "SELECT max(bill_number) FROM bills where company_id='{$_SESSION['client_company_id']}'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array_bill_number = mysqli_fetch_array ( $query1 );
	// Erzeugen
	$bill_number = $array_bill_number [0] + 1;
	
	// Read Userdata
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM client where client_id='{$_SESSION['client_user_id']}'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array_client = mysqli_fetch_array ( $query );
	
	$GLOBALS['mysqli']->query ( "INSERT INTO bills SET
	$paypal_add_mysql
		bill_number = '$bill_number',
		company_id  = '{$_SESSION['client_company_id']}',
		client_id   = '{$_SESSION['client_user_id']}',
		client_number= '{$array_client['client_number']}',
		company_1   = '{$array_client['company_1']}',
		company_2   = '{$array_client['company_2']}',
		title       = '{$array_client['title']}',
		gender      = '{$array_client['gender']}',
		firstname   = '{$array_client['firstname']}',
		secondname  = '{$array_client['secondname']}',
		street      = '{$array_client['street']}',
		zip         = '{$array_client['zip']}',
		city        = '{$array_client['city']}',
		country     = '{$array_client['country']}',
		date_create = NOW(),
		tel         = '{$array_client['tel']}',
		email       = '{$array_client['email']}',
		web         = '{$array_client['web']}',
		uid         = '{$array_client['uid']}',
		description = '{$array_client['description']}',
		text_after  = '{$array_client['text_after']}',
		discount    = '{$array_client['discount']}',
		no_mwst     = '{$array_client['no_mwst']}',
		brutto      = '$brutto_sum',
		netto       = '$netto_sum',
		paypal      = '{$_POST['paypal']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$bill_id = mysqli_insert_id($GLOBALS['mysqli']);
	
	foreach ( $_SESSION ['cart'] as $cart_id ) {
		$temp_id = $array [$cart_id] ['temp_id'];
		$title = $array [$cart_id] ['art_title'];
		$text = $array [$cart_id] ['art_text'];
		$count = $array [$cart_id] ['count'];
		$account = $array [$cart_id] ['account'];
		$netto = $array [$cart_id] ['netto'];
		$format = $array [$cart_id] ['format'];
		
		// Eintrage der Detail-Infos in die Datenbank
		$GLOBALS['mysqli']->query ( "REPLACE INTO bill_details SET
		bill_id   = '$bill_id',
		temp_id   = '$temp_id',
		art_nr    = (SELECT art_nr FROM article_temp WHERE temp_id = $temp_id),
		art_title = '$title',
		art_text  = '$text',
		count     = '$count',
		account   = '$account',
		netto     = '$netto',
		format    = '$format' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
	
	// Mail erzeugen als Bestätigung für die Bestellung
	$MailConfig ['to_email'] = $MailConfig ['bestellung'];
	$MailConfig ['from_email'] = $MailConfig ['bestellung'];
	// $MailConfig['to_email'] = 'martin@ssi.at';
	$MailConfig ['subject'] = "Artikel gekauft";

	$MailConfig ['message'] = "
	Rechnungsnummer: $bill_number
	Name/Firma: {$array_client['firstname']} {$array_client['secondname']}  {$array_client['company_1']}
	";
	require_once ('../../function.inc.php');
	send_mail ( '', $MailConfig['to_email'], $MailConfig['subject'], $MailConfig['message'] );
	
	// Remove Session
	unset ( $_SESSION ['cart'] );
	
	if ($_POST ['ajax']) {
		echo "<div align=center>";
		echo "<br>";
		echo $strShopTextAfterSend;
		echo "<br><br>";
		echo "<button  onclick=cart_back() class='button ui small'l>$strShopButtonBackHome</button>";
		echo "</div>";
		// echo "<button onclick=submit_order()>$strShopButtonSend</button>";
	}
} else {
	echo "<div align=center>";
	echo "<br>";
	echo "<font color=red>Sorry es gab einen Fehler bei der Bestellung!<br>Wir werden uns umgehend um das Problem k&uuml;mmern!</font>";
	echo "<br><br>";
	echo "<button  onclick=cart_back() class='button ui small'>$strShopButtonBackHome</button>";
	echo "</div>";
	
	// Mail erzeugen als Best�tigung f�r die Bestellung
	$MailConfig ['to_email'] = 'martin@ssi.at';
	$MailConfig ['subject'] = "Fehler bei Bestellung";
	$MailConfig ['message'] = "
	FirmenID: {$_SESSION['client_company_id']}
	ClientID: {$_SESSION['client_user_id']}
	";
	require_once ('../../function.inc.php');
	send_mail ( '', $MailConfig['to_email'], $MailConfig['subject'], $MailConfig['message'] );
}