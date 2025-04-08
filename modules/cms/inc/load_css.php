<?php
/*********************************************************************************************************************
 * UPDATE am 21.09.2020 mm
 * - Spezifische Seiten Optionen werden nun aus der smart_option direkt ausgelesen und nicht mehr aus dem array 
 * TODO: speichern der Elemente in eigener Options-db wie bei Seite und Elemente
 *********************************************************************************************************************/

include_once (__DIR__ . "/../library/css_umwandler.inc");

/**
 * ************************************************
 * Standard-Layout auslesen
 * ************************************************
 */
$sql = "SELECT * from smart_layout WHERE page_id = '{$_SESSION['smart_page_id']}'";
$query = $GLOBALS['mysqli']->query ( $sql );
$array_style = mysqli_fetch_array ( $query );
$layout_array = $array_style['layout_array']; // smart css
                                              
// Manuelle JQUERY
$jquery = $array_style['jquery'];
if ($jquery) {
	$GLOBALS['add_js2'] .= "$(document).ready(function() { {$array_style['jquery']} });";
}


// Auslesen der Werte fuer die Darstellung der Seite
$layout_array_n = explode ( "|", $layout_array );
foreach ( $layout_array_n as $array ) {
	$array2 = explode ( "=", $array );
	$GLOBALS[$array2[0]] = $array2[1];
}


$GLOBALS['menu_hidden'] = $array_style['menu_hidden'];

/**
 * ************************************************
 * Wird von den Einstellungen der Seiten abgerufen
 * ************************************************
 */

// $query = $GLOBALS['mysqli']->query ( "SELECT layout_array,split_representation,menubar_disable,breadcrumb_disable FROM smart_id_site2id_page WHERE site_id = '{$_SESSION ['site_id']}' " );
// $array_site = mysqli_fetch_array ( $query );

// $GLOBALS['split_representation'] = $array_site['split_representation'];
// $GLOBALS['menubar_disable'] = $array_site['menubar_disable'];
// $GLOBALS['breadcrumb_disable'] = $array_site['breadcrumb_disable'];


//new Version
call_smart_option ($_SESSION['smart_page_id'],$_SESSION ['site_id'],'',true);
//old version
// $layout_array = $array_site['layout_array']; // smart css
// $layout_array_n = explode ( "|", $layout_array );
// foreach ( $layout_array_n as $array_site ) {
// 	$array_site2 = explode ( "=", $array_site );
// 	$GLOBALS[$array_site2[0]] = $array_site2[1];
// }





if ($body_backgroundimage_site)
	$body_backgroundimage = $body_backgroundimage_site;

if ($header_backgroundimage_site)
	$header_backgroundimage = $header_backgroundimage_site;

$_SESSION['loginbar_color'] = $loginbar_color;
if ($loginbar)
	$_SESSION['show_loginbar'] = true;
else
	$_SESSION['show_loginbar'] = false;

// if ($middle_padding_top < 5)
// $middle_padding_top = '5';

if ($middle_padding_bottom === '')
	$middle_padding_bottom = '10';

if ($bread_padding_top === '')
	$bread_padding_top = '10';
if ($bread_padding_left_right === '')
	$bread_padding_left_right = '20';

// Abstand der Elemente zu einander
if ($content_element_margin_bottom === '')
	$content_element_margin_bottom = 10;

/**
 * **********************************************************
 * BODY
 * ***********************************************************
 */
if (! $body_fontsize or $body_fontsize > 30)
	$body_fontsize = '12';

if (! $body_fonthight)
	$body_fonthight = 0.5;
else
	$body_fonthight = $body_fonthight / 10;

$font_height = $body_fontsize + $body_fontsize * $body_fonthight;

// Wird vom Option-Site geladen
if ($body_backgroundimage2)
	$body_backgroundimage = $body_backgroundimage2;

if ($body_backgroundimage) {
	
	$body_backgroundimage = preg_replace ( '#/+#', '/', $body_backgroundimage );
	
	if (! $body_backgroundrepeat)
		$body_backgroundrepeat = 'no-repeat ';
	
	if (! $body_backgroundsize)
		$body_backgroundsize = 'auto ';
	
	if (! $body_backgroundposition)
		$body_backgroundposition = 'center top ';
	
	if ($body_fixed)
		$body_fixed = 'fixed ';
	
	$background = "background: url($body_backgroundimage) $body_backgroundrepeat $body_backgroundposition $body_fixed; background-size: $body_backgroundsize; ";
}

if ($head_backgroundimage2)
	$head_backgroundimage = $head_backgroundimage2;

$background .= "background-color: $body_backgroundcolor";

// if (! isset ( $body_border_on ))
// $body_border_on = 1;

// Wenn Border ausgeblendet wird
if ($body_border_on == '1' and ! $content_width_100) {
	if ($body_radius) {
		$body_radius2 = $body_radius + $body_border_size;
		$add_smart_content = "border-radius:{$body_radius2}px; ";
		$add_smart_content_header .= "border-radius:{$body_radius}px {$body_radius}px 0px 0px; ";
		$add_smart_content_footer .= "border-radius:0px 0px {$body_radius}px {$body_radius}px; ";
	}
	
	if ($body_shadow) {
		$add_smart_content .= 'box-shadow: 5px 5px 6px gray; ';
	}
	
	if ($body_border_color) {
		$add_smart_content .= "border: {$body_border_size}px solid $body_border_color; ";
	}
}

if ($menu_hidden) {
	$add_main_menu_field = " display:none; ";
}

if ($header_none) {
	$add_smart_content_header_hidden .= "display:none; ";
	
	// if ($body_radius)
	// $add_smart_content_body .= "border-radius:{$body_radius}px {$body_radius}px 0px 0px; ";
	
	$add_span_button_edit_head = "display:none; ";
}

if ($footer2_show) {
	$add_smart_content_footer2 .= "display:block; ";
} else
	$add_smart_content_footer2 .= "display:none; ";

/**
 * **********************************************************
 * BreadCrumb
 * ***********************************************************
 */
if (! $bread_visible)
	$add_breadcrumbe_field = 'display: none';

/**
 * **********************************************************
 * AUSGABE
 * ***********************************************************
 */

// max-width:$content_max_width; ->smart_content_container
// height:$footer_height2; -> fusszeile (social)
// height:$footer_height; -> fusszeile
function call_add_background($url) {
	if ($url)
		return "background-image: url($url);";
}

if ($body_font_google or $body_font_googlefamily) {
	if ($body_font_googlefamily)
		$body_font_google = $body_font_googlefamily;
	
	$body_font_google = explode ( ":", $body_font_google );
	$body_fontfamily = "'{$body_font_google[0]}', sans-serif";
	$font_google = preg_replace ( '/\+/', ' ', $body_font_google[0] );
	$font_google = "'$font_google', sans-serif";
	
	$font_google_link = preg_replace ( '/ /', '+', $body_font_google[0] );
	$set_output['css_google'] = "\n<link rel=\"stylesheet\" id='css_google' href=\"https://fonts.googleapis.com/css?family=$font_google_link\">";
}

if ($content_width_100) {
	$content_max_width_stretch = "100%";
} else {
	$content_max_width_stretch = $content_max_width;
	$content_max_width = '100%';
}

if ($header_background_no_repeat)
	$header_background_image_no_repeat = "background-repeat:no-repeat;";

if ($header_backgroundrepeat)
	$header_background_image_no_repeat = "background-repeat:$header_backgroundrepeat;";

if ($header_background_cover)
	$header_background_image_size = "background-size: cover;";

// Headerzusammenstellung
// if ($header_backgroundcolor and ! $header_backgroundcolor2) {
// 	$add_header_backgroundcolor .= "background-color:$header_backgroundcolor; background-image: url('$header_backgroundimage'); $header_background_image_size $header_background_image_no_repeat ";
// } elseif ($header_backgroundcolor and $header_backgroundcolor2 and $header_backgroundimage) {
// 	$add_header_backgroundcolor .= "background-color:$header_backgroundcolor; background-image: url('$header_backgroundimage'), linear-gradient($header_backgroundcolor, $header_backgroundcolor2); $header_background_image_size $header_background_image_no_repeat ";
// } elseif ($header_backgroundcolor and $header_backgroundcolor2) {
// 	$add_header_backgroundcolor .= "background-color:$header_backgroundcolor; background-image: linear-gradient($header_backgroundcolor, $header_backgroundcolor2);";
// }

$grid_content_padding = round ( $content_padding / 2 );

if ($header_height_auto) {
	$header_height = '';
}

if ($middle_backgroundrepeat)
	$middle_background_image_no_repeat = "background-repeat:$middle_backgroundrepeat;";

if ($footer_backgroundrepeat)
	$footer_background_image_no_repeat = "background-repeat:$footer_backgroundrepeat;";

// Zieht den Inhalt auf 100%
if ($element_fullsize_all) {
	$content_max_width = '';
}

// if ($textfield_div_padding)
// $set_style .= ".textfield_div { padding:$textfield_div_padding" . "px !important;; }";

if ($textfield_div_margin)
	$set_style .= ".column>.textfield_div { margin-bottom:$textfield_div_margin" . "px !important; }";

$set_style .= "
body { $background  }
body.pushable>.pusher { $background }
h1, h2, h3, h4 { font-family:$body_fontfamily;
	color:
	$body_fontcolor;
}
a:link {text-decoration: none;
	color:
	$body_a_link;
}
a:visited {text-decoration: none;
	color:
	$body_a_visited;
}
a:hover {text-decoration: none;
	color:
	$body_a_hover;
}


.top_smart_content { padding-top:$content_margin_top;  }
.segment_field.segment.ui{ font-size:$body_fontsize; }
.smart_content_fix {   max-width:$content_max_width_stretch; }
.smart_content { max-width:$content_max_width_stretch;  $add_smart_content }
.menu_item_a {font-family:$body_fontfamily;}
.smart_content_container,.smart_content_container>p {  line-height:$font_height; color:$body_fontcolor; font-family:$body_fontfamily; }
.smart_content_body   { background-color:$middle_backgroundcolor;" . call_add_background ( $middle_backgroundimage ) . "$middle_background_image_no_repeat }
.smart_content_footer2 { background-position:$footer_backgroundposition2; background-color:$footer_backgroundcolor2;" . call_add_background ( $footer_backgroundimage2 ) . "$add_smart_content_footer2   }
.smart_content_element { max-width:$content_max_width;  }
.smart_body_top {  padding-top:$middle_padding_top; }
.textfield { font-size:$body_fontsize; }
.smart_body_bottom {  padding-bottom:$middle_padding_bottom; }
.element_padding { padding-left: $content_padding" . "px; padding-right: $content_padding" . "px; }
.element_margin_splitter { margin-left: $content_padding" . "px; margin-right: $content_padding" . "px; }
.breadcrumb_field { padding-top:$bread_padding_top; padding-left:$bread_padding_left_right;  padding-right:$bread_padding_left_right; $add_breadcrumbe_field }
" . $array_style['style'];

	
	//.smart_content_fix { width:100%; right: 0; left: 0; margin-right: auto; margin-left: auto; position:fixed; bottom:-1px; z-index:2000;  max-width:$content_max_width_stretch; }
	
	
// $set_style .= ".smart_content_footer { background-position:$footer_backgroundposition; background-color:$footer_backgroundcolor;" . call_add_background ( $footer_backgroundimage ) . " $footer_background_image_no_repeat $add_smart_content_footer }";

// if ($grid_padding_left)
// $set_style .= ".ui.grid > .column:not(.row), .ui.grid > .row > .column { padding:0px;}";

// #header0 { max-width:$content_max_width_stretch; }

// $set_style .= ".smart_content_header { background-position:$header_backgroundposition; $add_header_backgroundcolor height:$header_height; $add_header_padding $add_smart_content_header $add_smart_content_header_hidden}";

//$add_header_backgroundcolor
$set_style .= "
	#header2>.segment_field:first-of-type { $add_smart_content_header }
	#header2>.segment_field:first-of-type>.sort_div_field { $add_smart_content_header }
";
$set_style .= "
	#footer>.segment_field:last-of-type { $add_smart_content_footer }
	#footer>.segment_field:last-of-type>.sort_div_field { $add_smart_content_footer }
";

if ($left_0_div_padding_left) {
	$set_style .= "#left_0>div !important { padding-left: {$left_0_div_padding_left}px; padding-right: {$left_0_div_padding_left}px;  }";
}

if ($left_0_div_padding_top) {
	// $set_style .= "#left_0>div { padding-top: {$left_0_div_padding_top}px; padding-bottom: {$left_0_div_padding_top}px; }";
	$set_style .= "#left_0>div:not(:last-child) { margin-bottom: {$left_0_div_padding_top}px  !important;  }";
}

// include(__DIR__.'/load_css_menu.php');

$set_style = css_umwandeln ( $set_style );

$set_style .= "@media only screen and (max-width: 767px) { .smart_content_layer { display: none; } }";
// Ausblenden in Smartphone, wenn im Design angegeben wurde
if ($header_mobile_disable) {
	$set_style .= "@media only screen and (max-width: 767px) { .smart_content_header { display: none; } }";
	$set_style .= "@media only screen and (max-width: 767px) { .menu_field {  padding:0px; } }";
}
