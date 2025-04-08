<?php
/*
 * $content: wird direkt übergeben auf die Funktion (Siehe functions.php)
 * $new: Wenn neues Textfeld erzeugt wird, soll dieses Editierbar sein
 * $layer_id: ID des jeweiligen Layers
 */

// content auslesen
$query = $GLOBALS['mysqli']->query ( "
		SELECT text FROM smart_langLayer
		WHERE lang='{$_SESSION['page_lang']}'
		AND fk_id = '$layer_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );
$content = $array['text'];

// Verkleinert ein Bild für schnellere Ladezeiten
// TODO: Automatische Bildverkleinerung  - läuft nicht!
// $content = change_resize ( $content );


if ($new) {
	$output_add = "<script>save_content_id('layer_text$layer_id'); $('.layer_button_textfield').css('visibility','visible');</script> ";
}


if ($_SESSION['add_contenterditble']) {
	$add_contenterditble = "id='layer_text$layer_id' contenteditable=true";
}

//$ckeditor5 = "id='layer_text$layer_id' class='.editor5' ";

//$output = "<div class='ui middle aligned grid'><div class='eight column wide'><div $add_contenterditble>$content</div></div></div>";
$output = "<div class='smart_content_container textfield' $add_contenterditble>$content</div>";