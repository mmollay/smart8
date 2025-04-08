<?php
// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');


$gadget = $GLOBALS['mysqli']->real_escape_string ( $_POST['gadget'] );
$value = $_POST['value'];
$id = $GLOBALS['mysqli']->real_escape_string ( $_POST['id'] );
$layer_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['update_id'] );

$player = $_POST['player'];

// Wandelt youtube_link in kurzcode um
if ($player == 'youtube') {
	if (strlen ( $value ) > 12) {
		$value = parse_youtube ( $value );
	}
} elseif ($player == 'vimeo') {
	// Wenn nur Code eingegeben wird, sonst umwandeln
	if (! is_numeric ( $value )) {
		$value = parse_vimeo ( $value );
	}
}

echo $value;
?>