<?php
session_start();

// Zugangsdaten fuer die Datenbank
require_once '../config.php';
require_once '../../smart_form/fu_filelist.php';

// Nur speichern wenn User_id vorhanden ist
if ($_SESSION['user_id'] < 1 )
	exit;

foreach ($_POST as $key => $value) {
    if ($value) {
        $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
    }
}echo $gender;

$GLOBALS['mysqli']->query("UPDATE ssi_company.user2company SET 
		firstname  = '$firstname',
		secondname = '$secondname',
		zip  = '$zip',
		city = '$city',
		gender = '$gender',
		street = '$street',
		telefon = '$telefon',
		country = '$country',
		birthday  = '$birthday'
		WHERE user_id = '{$_SESSION['user_id']}'
		") or die(mysqli_error($GLOBALS['mysqli']));
