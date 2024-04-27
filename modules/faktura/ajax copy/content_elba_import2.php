<?php
/**
 * ****************************************
 * IMPORT für Elba-Daten
 * 04.01.2020 mm@ssi.at
 * ****************************************
 */
require_once ('../config.inc.php');

$setTEXT = $_POST ['setTEXT'];
$setDelimiter = $_POST ['setDelimiter'];
$update = $_POST ['update'];
$account = $_POST ['accountnumber'];
$data = $_POST ['file_data'];
$remove_list = $_POST ['remove_list'];

if (! $account) {
	echo "Kontonummer ist nicht definiert!";
	exit ();
}

// $setTemplate = array_keys ( $array_import );
$columns = array ('date','text','booking_date','amount','unit','timestamp' );

// Import Data-file
if ($data) {
	if ($_SERVER ['SERVER_NAME'] == 'localhost') {
		$upload_dir = "/Applications/XAMPP/xamppfiles/htdocs/smart_users/faktura/";
	} else {
		$upload_dir = "/var/www/ssi/smart_users/faktura/";
	}

	if (is_file ( $upload_dir . $data ))
		$line = file ( $upload_dir . $data );
} elseif ($setTEXT) {
	$line = explode ( "\n", $setTEXT );

	$count_user_first = count ( $line );
	$line = array_unique ( $line );
	$line = array_filter ( $line );
}

// Set Delimter "tab"
if ($setDelimiter == 'tab') {
	$setDelimiter = "\t";
}
// Default -Delimiter
if (! $setDelimiter)
	$setDelimiter = ';';

$count_new = 0;
$count_exists = 0;

if ($remove_list) {
	//echo $line[1];
	$array_fields = explode ( ';', $line [1] );
	$last_date_for_remove = date_german2mysql($array_fields [0]);
	$sql_delete = "DELETE FROM data_elba WHERE account = '$account' AND date >= '".$last_date_for_remove."' AND connect_id = 0  ";
	$GLOBALS ['mysqli']->query ( $sql_delete ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
}


if ($line) {
	foreach ( $line as $value ) {
		//$value = utf8_encode ( $value );
		$ii = 0;
		$add_mysql = '';
		// Split for templates with "delimiter"
		$array_fields = explode ( $setDelimiter, $value );
		foreach ( $array_fields as $fields ) {
			if ($columns [$ii]) {
				if ($columns [$ii] == 'date' or $columns [$ii] == 'booking_date')
					$fields = date_german2mysql ( $fields );
				if ($columns [$ii] == 'amount') {
					$fields = nr_format2english ( $fields );
				}
				if ($columns [$ii]) {
					$fields = preg_replace ( "/\"/", '', $fields );
				}
				$add_mysql .= ", $columns[$ii] = \"$fields\" ";
				$ii ++;
			}
		}
		// echo $add_mysql;
		$sql = "INSERT IGNORE INTO data_elba SET `account`  = '$account' $add_mysql ";
		// echo $sql;

		$GLOBALS ['mysqli']->query ( $sql ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );

		if (mysqli_insert_id ( $GLOBALS ['mysqli'] ) != 0)
			$count_new ++;
		else
			$count_exists ++;
	}
}

echo "Kontonummer: $account<br>";
echo "Neue Einträge: $count_new<br>";
echo "Bereits von dieser Datei vorhandene Einträge: $count_exists<br>";
echo "<br><b>Import abgeschlossen!</b>";

