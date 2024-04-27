<?php
session_start ();
require ("../config.inc.php");

$subject = $_POST ['subject'];

if ($_SESSION ['company_id']) {
	$GLOBALS['mysqli']->query ( "UPDATE company SET subject = '$subject' WHERE company_id = '{$_SESSION['faktura_company_id']}' " );
} else
	echo "error";