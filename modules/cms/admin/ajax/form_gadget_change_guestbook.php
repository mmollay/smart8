<?php
// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');


if ($_POST['guestbook_id'] == 'new')
	$_POST['guestbook_id'] = ''; // es efolgt ein neuer Eintrag


$GLOBALS['mysqli']->query ( "REPLACE INTO smart_gadget_guestbook SET
	guestbook_id = '{$_POST['guestbook_id']}' ,
	page_id      = '{$_SESSION['smart_page_id']}',
	title        = '{$_POST['guestbook_name']}'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$_POST[guestbook_id] = mysqli_insert_id ( $GLOBALS['mysqli'] );

echo $_POST[guestbook_id];