<?php
/*
 * Auslesen aktueller Positionen
 */
require ("../config.inc.php");

// Check ob Eintrag bereits vorhanden ist
// Art_NR UND Art_Title
$temp_id = $_POST['temp_id'];

if ($_POST['modus'] == 'add_temp' or ! $temp_id) {
	$check_art_nr = mysql_singleoutput ( "SELECT * FROM article_temp WHERE art_nr = '{$_POST['art_nr']}' ", "art_nr" );
	$check_title = mysql_singleoutput ( "SELECT * FROM article_temp WHERE art_title = '{$_POST['art_title']}' ", "art_title" );
	$_POST['temp_id'] = '';
}

$netto = preg_replace ( "/,/", ".", $_POST['netto'] );

if ($check_art_nr) {
	echo 'double_art_nr';
} elseif ($check_title) {
	echo 'double_title';
} elseif (! $_POST['art_nr']) {
	echo 'empty_art_nr';
} elseif (! $_POST['art_title']) {
	echo 'empty_title';
} elseif ($_POST['modus'] == 'add_temp') {
	// Template anlegen
	$GLOBALS['mysqli']->query ( "INSERT INTO article_temp SET
	format = '{$_POST['format']}',
	count  = '{$_POST['count']}',
	art_nr = '{$_POST['art_nr']}',
	art_title = '{$_POST['art_title']}',
	art_text  = '{$_POST['art_text']}',
	account   = '{$_POST['account']}',
	netto     = '$netto'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$art_id = mysqli_insert_id ( $GLOBALS['mysqli'] );
	echo "ok";
	
} elseif ($_POST['modus'] == 'mod_temp') {
	// Template anlegen
	$GLOBALS['mysqli']->query ( "UPDATE article_temp SET
	format = '{$_POST['format']}',
	count  = '{$_POST['count']}',
	art_nr = '{$_POST['art_nr']}',
	art_title = '{$_POST['art_title']}',
	art_text  = '{$_POST['art_text']}',
	account   = '{$_POST['account']}',
	netto     = '$netto'
	WHERE temp_id    = '{$_POST['temp_id']}'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	echo "ok";
}
?>