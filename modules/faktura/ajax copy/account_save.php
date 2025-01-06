<?php
/*
 * Einpflegen der Daten in die Datenbank
 * mm@ssi.at am 05.07.2012
 * UPDATE: seperate update und INSERT
 */
session_start ();
require ("../config.inc.php");

if ($_POST ['account_id']) {
	$GLOBALS['mysqli']->query ( "UPDATE accounts SET
	company_id  = '{$_POST['company_id']}',
	code        = '{$_POST['code']}',
	title       = '{$_POST['title']}',
	afa_400     = '{$_POST['afa_400']}',
	accountgroup_id = '{$_POST['accountgroup_id']}'
	WHERE account_id  = '{$_POST['account_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo "update";
} else {
	$GLOBALS['mysqli']->query ( "INSERT INTO accounts SET
	account_id  = '{$_POST['account_id']}',
	company_id  = '{$_POST['company_id']}',
	code        = '{$_POST['code']}',
	title       = '{$_POST['title']}',
	tax         = '{$_POST['tax']}',
	`option`    = '{$_POST['option']}',
	afa_400     = '{$_POST['afa_400']}',
	accountgroup_id = '{$_POST['accountgroup_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	echo "ok";
}
?>