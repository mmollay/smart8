<?php
// Datenbankverbindung herstellen
include_once ('../../../login/config_main.inc.php');
require ('../../config.inc.php');

$page_id = $_SESSION['smart_page_id'];
$layout_id = $_POST['layout_id'];
$matchcode = $_POST['matchcode'];
$component = $_POST['component'];
$style = $GLOBALS['mysqli']->real_escape_string ( $_POST['style'] );
$jquery = $GLOBALS['mysqli']->real_escape_string ( $_POST['jquery'] );

// bestehenden auslesen
$sql = $GLOBALS['mysqli']->query ( "SELECT layout_array FROM smart_layout WHERE page_id = '$page_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$array = mysqli_fetch_array ( $sql );
$array_layout = $array[0];

if ($component == 'background_head') {
	$new_array_layout = array ( header_backgroundcolor , header_backgroundimage , header_height , header_height_auto , header_padding , header_backgroundcolor2 );
} else {
	$new_array_layout = array ( 
			// MENU
			menu_seperation_line ,
			menu_hidden ,
			menu_a_hover_bgcolor ,
			menu_current_bgcolor ,
			menu_current_color ,
			select_menu_layout ,
			menu_backgroundcolor ,
			menu_backgroundcolor2 ,
			menu_a_link ,
			menu_ul_a_hover ,
			menu_ul_a_link ,
			menu_a_hover ,
			menu_a_visited ,
			menu_padding_top ,
			menu_padding_bottom ,
			menu_padding_left_right ,
			menu_radius ,
			menu_border_size ,
			menu_border_color ,
			menu_border ,
			menu_padding_a ,
			menu_fontsize ,
			menu_shadow ,
			body_fontcolor ,
			body_fontsize ,
			body_fontfamily ,
			body_font_google ,
			body_radius ,
			body_border_size ,
			body_shadow ,
			body_border_color ,
			body_a_link ,
			body_a_hover ,
			body_a_visited ,
			body_backgroundcolor ,
			header_backgroundcolor ,
			header_backgroundcolor2 ,
			header_none ,
			middle_backgroundcolor ,
			footer_backgroundcolor ,
			footer_backgroundcolor2 ,
			footer2_show ,
			body_backgroundimage ,
			header_backgroundimage ,
			header_parallax ,
			middle_backgroundimage ,
			footer_backgroundimage ,
			footer_backgroundimage2 ,
			header_height ,
			header_height_auto ,
			footer_height ,
			footer_height2 ,
			content_margin_top ,
			content_padding ,
			content_max_width ,
			middle_padding_top ,
			middle_padding_bottom ,
			bread_visible ,
			bread_padding_top ,
			bread_padding_left_right ,
			sitebar ,
			loginbar ,
			loginbar_color ,
			content_width_100 );
}

foreach ( $new_array_layout as $key ) {
	$value = $GLOBALS['mysqli']->real_escape_string ( $_POST[$key] );
	$array_layout = generate_array ( $key, $value, $array_layout );
	// $array_new .= $key."=".$value."|";
	// $array_new = preg_replace("/|/","",$array_new);
}

if (! $layout_id) {
	$GLOBALS['mysqli']->query ( "REPLACE INTO smart_layout SET
	layout_id  = '$layout_id',
	matchcode  = '$matchcode',
	page_id    = '$page_id',
	style      ='$style',
	jquery     = '$jquery',
	layout_array = '$array_layout'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
} else {
	if ($array_layout)
		$GLOBALS['mysqli']->query ( "UPDATE smart_layout SET layout_array = '$array_layout' WHERE layout_id  = '$layout_id' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	
	if ($component != 'background_head')
		$GLOBALS['mysqli']->query ( "UPDATE smart_layout SET style ='$style', jquery ='$jquery' WHERE layout_id  = '$layout_id' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}

set_update_site ( 'all' );

echo "ok";
?>