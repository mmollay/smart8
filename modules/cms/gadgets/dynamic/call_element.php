<?php
session_start ();
include ('../config.php');
include ('../function.inc.php');

//$gaSql['link'] = mysql_pconnect ( $cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'] ) or die ( 'Could not open connection to server' );
//mysql_select_db ( $cfg_mysql['db'], $gaSql['link'] ) or die ( 'Could not select database ' . $cfg_mysql['db'] );

$layer_id = $_POST['id'];

// User_ID von der jeweiligen Webseite wo das dynamische Element eingebunden ist
$GLOBALS['client_user_id'] = $_POST['user_id'];

$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_layer LEFT JOIN smart_langLayer ON smart_layer.layer_id=smart_langLayer.fk_id WHERE layer_id = '$layer_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// AND lang='{$_SESSION['page_lang']}'
$array = mysqli_fetch_array ( $query );

$gadget = $array['gadget'];

if (!$gadget) $gadget = 'textfield';

//Ausgabe bei Textfeld
if ($gadget == 'textfield') {
	// wizard wandelt (client_name .... um zu realen Werten)
	echo preg_replace_callback ( "/\{%(.*?)%\}/", "callback_wizard", $array['text'] );
}
//Ausgabe bei anderen ELementen
else {
	// parameter aufrufen aus der Datenbank
	$sql = $GLOBALS['mysqli']->query ( "SELECT gadget_array from smart_layer WHERE layer_id = '$layer_id'" );
	$array = mysqli_fetch_array ( $sql );
	$gadget_array = $array['gadget_array'];
	$gadget_array_n = explode ( "|", $gadget_array );
	if ($array['gadget_array']) {
		foreach ( $gadget_array_n as $array ) {
			$array2 = preg_split ( "[=]", $array, 2 );
			${$array2[0]} = $array2[1];
		}
	}
	
	// Templates wie Title, sitemap,usw...
	if ($gadget == 'other') {
		include ('../templates/include.php');
	}  // Alles anderen Gadgets werden direkt über die Includes abgerufen
	else {	
		include ("../$gadget/include.inc.php");
	}
}

/*
 * Ruft die Verknüpfungen vom Wizard auf
 * Vorname, Nachname, Texte
 */
function callback_wizard($matches) {
	$query = $GLOBALS['mysqli']->query ( "SELECT * from ssi_company.user2company WHERE user_id = '{$GLOBALS['client_user_id']}' " );
	$array = mysqli_fetch_array ( $query );
	if ($matches[1] == 'client_name')
		return $array['firstname'] . " " . $array['secondname'];
	if ($matches[1] == 'client_firstname')
		return $array['firstname'];
	if ($matches[1] == 'client_secondname')
		return $array['secondname'];
	if ($matches[1] == 'client_email')
		return $array['user_name'];
	if ($matches[1] == 'client_verify_key')
		return $array['verify_key'];
	if ($matches[1] == 'client_token')
		return $array['verify_key'];
}