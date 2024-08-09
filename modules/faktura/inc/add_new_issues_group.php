<?php
/*
 * Speichert ein neue Grupppe in issues_group!
 * mm@ssi.at am 10.10.2015
 */
include (__DIR__ . '/../f_config.php');

$name = $GLOBALS['mysqli']->real_escape_string($_POST['name']);

$query = $GLOBALS['mysqli']->query("SELECT name FROM issues_group WHERE name =  '$name' order by timestamp desc") or die(mysqli_error());
// Prüft ob Gruppe bereits vorhanden ist
if (!mysqli_num_rows($query)) {
	$GLOBALS['mysqli']->query("INSERT INTO issues_group SET name = '$name', company_id = '{$_SESSION['faktura_company_id']}' ") or die(mysqli_error());
	echo mysqli_insert_id($GLOBALS['mysqli']);

} else
	echo "exist";