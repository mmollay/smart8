<?php
session_start ();
include ('../config.php');
include ('../function.inc.php');
include ('../../library/functions.php');

$gaSql['link'] = mysql_pconnect ( $cfg_mysql['server'], $cfg_mysql['user'], $cfg_mysql['password'] ) or die ( 'Could not open connection to server' );
mysql_select_db ( $cfg_mysql['db'], $gaSql['link'] ) or die ( 'Could not select database ' . $cfg_mysql['db'] );

$site_id = $_POST['id'];
$position = $_POST['position'];
$dynamic_user_id = $_POST['dynamic_user_id'];
$dynamic_page_id = $_POST['dynamic_page_id'];

// User_ID von der jeweiligen Webseite wo das dynamische Element eingebunden ist
$GLOBALS['client_user_id'] = $_POST['user_id'];
$GLOBALS['dynamic_path'] = "../../../smart_users/ssi/user$dynamic_user_id/explorer/$dynamic_page_id";

$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_layer LEFT JOIN smart_langLayer ON smart_layer.layer_id=smart_langLayer.fk_id WHERE site_id = '$site_id' AND position = '$position' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// AND lang='{$_SESSION['page_lang']}'
while ( $array = mysqli_fetch_array ( $query ) ) {
	$gadget = $array['gadget'];
	$layer_id = $array['layer_id'];
	if (!$gadget)  $gadget = 'textfield';
	// Ausgabe bei Textfeld
	if ($gadget == 'textfield') {
		// wizard wandelt (client_name .... um zu realen Werten)
		echo preg_replace_callback ( "/\{%(.*?)%\}/", "callback_wizard", $array['text'] );
	} else { // Ausgabe bei anderen ELementen
		$GLOBALS['set_ajax'] = true;
		echo show_element ( $layer_id);
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