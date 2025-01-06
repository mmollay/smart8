<?php
/*
 * Auslesen aktueller Positionen
 */
require ("../config.inc.php");

// Check ob Eintrag bereits vorhanden ist
// Art_NR UND Art_Title
$temp_id = $_POST ['temp_id'];

if ($_POST ['modus'] == 'add_temp' or ! $temp_id) {
	$check_art_nr = mysql_singleoutput ( "SELECT * FROM article_temp WHERE company_id = '{$_SESSION['faktura_company_id']}' AND art_nr = '{$_POST['art_nr']}' ", "art_title" );
	$check_title = mysql_singleoutput ( "SELECT * FROM article_temp WHERE company_id = '{$_SESSION['faktura_company_id']}' AND art_title = '{$_POST['art_title']}' ", "art_title" );
	$_POST ['temp_id'] = '';
}

$netto = preg_replace ( "/,/", ".", $_POST ['netto'] );

if ($check_art_nr) {
	echo 'double_art_nr';
} elseif ($check_title) {
	echo 'double_title';
} elseif (! $_POST ['art_nr']) {
	echo 'empty_art_nr';
} elseif (! $_POST ['art_title']) {
	echo 'empty_title';
} else {
	// Template anlegen
	$GLOBALS['mysqli']->query ( "REPLACE INTO article_temp SET
	temp_id    = '{$_POST['temp_id']}',
	company_id = '{$_SESSION['faktura_company_id']}',
	format = '{$_POST['format']}',
	count  = '{$_POST['count']}',
	art_nr = '{$_POST['art_nr']}',
	art_title = '{$_POST['art_title']}',
	art_text  = '{$_POST['art_text']}',
	account   = '{$_POST['account']}',
	netto     = '$netto',
	internet_title = '{$_POST['internet_title']}',
	internet_text = '{$_POST['internet_text']}',
	internet_show = '{$_POST['internet_show']}',
	internet_inside_title = '{$_POST['internet_inside_title']}',
	internet_inside_text = '{$_POST['internet_inside_text']}',
	gallery = '{$_POST['gallery']}',
	gallery_inside = '{$_POST['gallery_inside']}',
	group_id = '{$_POST['group_id']}'
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$art_id = mysqli_insert_id ($GLOBALS['mysqli']);
	echo $art_id;
	
	if ($_POST ['groups']) {
		// Remove old connects
		$GLOBALS['mysqli']->query ( "DELETE FROM article2group WHERE article_id = '$art_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		foreach ( $_POST ['groups'] as $group_value ) {
			$GLOBALS['mysqli']->query ( "INSERT INTO article2group SET article_id = $art_id, group_id = $group_value " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		}
	}
}

?>