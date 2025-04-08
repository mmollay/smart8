<?php

// Connect to database
include_once ('../../../login/config_main.inc.php');
include ('../../gadgets/inc/phpthumb/ThumbLib.inc.php');
// include_once ('../../library/functions.php');

// $_POST ['content'] = image_resizer ( $_POST ['content'] );
$content = $GLOBALS['mysqli']->real_escape_string ( $_POST['content'] );

if (! $_SESSION['smart_page_id']) {
	echo "'smart_page_id' nicht definiert";
	exit ();
}

if (! $_SESSION['site_id']) {
	echo "'site_id' nicht definiert";
	exit ();
}

/**
 * *****************************************
 * check content vom Fomular
 * ****************************************
 */
if (preg_match ( "/field-\.*/", $_POST['content_id'] )) {
	$zeichen = preg_split ( '/field-/', $_POST['content_id'] );
	$site_id = $_SESSION['site_id'];
	$field_id = $zeichen[1];
	$query = $GLOBALS['mysqli']->query ( "SELECT site_id FROM smart_layer a LEFT JOIN smart_formular b ON a.layer_id = b.layer_id WHERE field_id = '$field_id' and site_id = '≈' " );
	$check = mysqli_num_rows ( $query );
	if ($check) {
		$GLOBALS['mysqli']->query ( "UPDATE smart_formular SET text = '$content' WHERE field_id = '$field_id' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		echo "ok";
	}
} else if (preg_match ( "/layer_text\.*/", $_POST['content_id'] )) {
	// check layer oder content from Site
	$zeichen = preg_split ( '/layer_text/', $_POST['content_id'] );
	$layer_id = $zeichen[1];
	// Save Content
	$GLOBALS['mysqli']->query ( "REPLACE INTO smart_langLayer SET
	fk_id = '$layer_id',
	lang = '{$_SESSION['page_lang']}',
	text = '$content'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	echo "ok";
}

set_update_site ('',$layer_id);