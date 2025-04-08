<?php
/*
 * Martin Mollay 08.06.2017
 * @verify_key .......Key des jeweiligen User 
 * @link .............Weiterleitung zum Page
 * Hier word der Client_verify_key für die jeweilige Seite gesetzt, danach weitergeleitet:
 * Sollte Weiterseite nicht funktionieren, dann steht ein Link zur Verfügung!
 */

//localhost/pages/set_client.php?token=8514cfb0a64d66d51893753df4a864a5278eccaa30c7856670712bac4074&link=http://www.ssi.at

//localhost/pages/set_client.php?token=8514cfb0a64d66d51893753df4a864a5278eccaa30c7856670712bac4074&link=http://localhost/smart_users/ssi/user40/page13

//center.ssi.at/pages/set_client.php?token=ec6dc6528aab12156ecab0f0472532ec&link=http://www.ssi.at

//center.ssi.at/pages/set_client.php?token={%token%}&link=http://www.ssi.at
//localhost/pages/set_client.php?token={%token%}&link=http://localhost/smart_users/ssi/user40/page13

session_start ();
if ($_GET['link'] && $_GET['token']) {
	//übergeben der client_verify_key
	$_SESSION['client_token'] = $_GET['token'];
	setcookie ( "client_token", $_SESSION['client_token'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );
	//echo $_SESSION['client_token'];
	header ( "Location: {$_GET['link']}" );
	echo "<a href='{$_GET['link']}'> [hier klicken]</a>";
}
else {
	echo "Token wurden nicht korrekt übergeben.";
}