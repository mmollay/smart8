<?php
require_once ('../config.inc.php');
// require_once ('../functions.inc.php');

$_POST ['activate'] = 1;

$setTEXT = $_POST ['setTEXT'];
$setDelimiter = $_POST ['setDelimiter'];
$update = $_POST ['update'];
$import_contact = true;

// $setTemplate = "email,firstname,secondname,gender,title,company_1,company_2,client_number,city,zip,country,tel,web";
$setTemplate = array_keys ( $array_import );

if (! $setTEXT) {
	echo "<br><br>Keine Daten zum Import vorhanden!";
	return;
}

$line = explode ( "\n", $setTEXT );

$count_user_first = count ( $line );
$line = array_unique ( $line );
$line = array_filter ( $line );

// Set Delimter "tab"
if ($setDelimiter == 'tab') {
	$setDelimiter = "\t";
}

/**
 * *********************************
 * Kontakte auflösen und zum importieren vorbereiten
 * **********************************
 */

// Auslesen der Feldnamen fuer die Benennung der Felder
$columns = $setTemplate;

if ($line2) {
	foreach ( $line2 as $value ) {
		$ii = 0;

		// Split for templates with "delimiter"
		$array_fields = explode ( $setDelimiter, $value );
		foreach ( $array_fields as $fields ) {

			$send_array [$array_fields [0]] [$columns [$ii]] = $fields;
			$_POST [$columns [$ii]] = trim ( $fields );
			$ii ++;
		}
		include ('../inc/contact_save.php');
	}
	echo "<b>Import abgeschlossen:</b><br><br>";
}

if ($line_error) {
	$ii = 0;
	foreach ( $line_error as $value ) {
		$wrong_email ++;
	}
}

if ($new_user == 1)
	echo "$new_user Kunde wurde angelegt <br>";
elseif ($new_user > 1)
	echo "$new_user Kunde wurden angelegt <br>";

if ($updated_user == 1)
	echo "$updated_user Kunde wurde überschrieben<br>";
elseif ($updated_user > 1)
	echo "$updated_user Kunde wurden überschrieben<br>";

if ($exist_user == 1)
	echo "$exist_user Kunde besteht bereits<br>";
elseif ($exist_user > 1)
	echo "$exist_user Kunde bestehen bereits<br>";

if ($wrong_email) {
	echo "<font color =red>$wrong_email nicht gültige Email(s) erkannt oder Delimiter falsch gesetzt</font>";
}