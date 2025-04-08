<?php
include_once (__DIR__ . "/../../library/css_umwandler.inc");

if (! $menu_ul_a_hover)
	$menu_ul_a_hover = 'black';

if (! $menu_ul_a_link)
	$menu_ul_a_link = 'black';

// Default - Border (around)
if (! $menu_border) {
	$menu_border = 'border-bottom';
	$menu_border_size = '1';
	$menu_border_color = '#EEE';
}

// Trennlinien zwischen den Menüpunkten
if ($menu_seperation_line)
	$add_sm_default_seperation_line = ".sm > li { border-left: 1px solid $menu_seperation_line; }";

if ($menu_radius) {
	$menu_radius2 = $menu_radius + $menu_border_size;
	$menu_radius1 = $menu_radius - 1;
	$add_sm_default = "border-radius:{$menu_radius2}px; ";
	$add_sm_default_li = "border-radius:{$menu_radius1}px 0 0 {$menu_radius1}px;";
}

if ($menu_border_color or $menu_border_size) {
	$add_sm_default .= "$menu_border: {$menu_border_size}px solid $menu_border_color; ";
}

// Menu-Shadow
if ($menu_shadow) {
	$add_sm_default .= "
	-moz-box-shadow:0 1px 1px rgba(0,0,0,0.3);
	-webkit-box-shadow:0 1px 1px rgba(0,0,0,0.3);
	box-shadow:0 1px 1px rgba(0,0,0,0.3);
	";
}

if (! $menu_backgroundcolor)
	$menu_backgroundcolor_ul = '#FFF';
else
	$menu_backgroundcolor_ul = $menu_backgroundcolor;

if ($menu_backgroundcolor and ! $menu_backgroundcolor2) {
	$add_sm_default .= "background-color:$menu_backgroundcolor;)";
} elseif ($menu_backgroundcolor and $menu_backgroundcolor2) {
	$add_sm_default .= "
	background-image:-moz-linear-gradient(top,$menu_backgroundcolor 0%,$menu_backgroundcolor2 100%);
	background-image:-webkit-gradient(linear,left top,left bottom,color-stop(0%,$menu_backgroundcolor),color-stop(100%,$menu_backgroundcolor2));
	background-image:-webkit-linear-gradient(top,$menu_backgroundcolor 0%,$menu_backgroundcolor2 100%);
	background-image:-o-linear-gradient(top,$menu_backgroundcolor 0%,$menu_backgroundcolor2 100%);
	background-image:-ms-linear-gradient(top,$menu_backgroundcolor 0%,$menu_backgroundcolor2 100%);
	background-image:linear-gradient(top,$menu_backgroundcolor 0%,$menu_backgroundcolor2 100%);";
}

if ($menu_padding_left_right) {
	$menu_padding_left = $menu_padding_right = $menu_padding_left_right;
}

if (! $menu_current_bgcolor)
	$menu_current_bgcolor = 'transparent';
if (! $menu_current_color) {
	$menu_current_color = '';
	$add_current = ' font-weight:bold ';
}
if (! $menu_a_hover_bgcolor)
	$menu_a_hover_bgcolor = 'transparent';

if ($menu_button_padding_left_right)
	$menu_button_padding_left_right = "padding-left:" . $menu_button_padding_left_right . "px; padding-right:" . $menu_button_padding_left_right . "px; ";

$set_style .= "
	.sm a, .sm a:hover, .sm a:focus, .sm a:active { font-family:$body_fontfamily; $menu_padding_a  }
	.sm ul a, .sm ul a:hover, .sm ul a:focus, .sm ul a:active {  $menu_padding_ul_a }
	.sm {background-color:$menu_backgroundcolor; $add_sm_default }
	.sm ul a { font-size: $menu_fontsize; background-color:white; color:$menu_ul_a_link;}
	.sm ul a:hover { font-size: $menu_fontsize;  background-color:#EEE; color:$menu_ul_a_link; }
	.sm ul a:visited {font-size: $menu_fontsize; text-decoration: none; color:$menu_ul_a_link;}
	.sm a { $menu_button_padding_left_right font-size: $menu_fontsize; text-decoration: none; color:$menu_a_link;}
	.sm a:visited { $menu_button_padding_left_right font-size: $menu_fontsize; text-decoration: none; color:$menu_a_link;}
	.sm a:hover { $menu_button_padding_left_right font-size: $menu_fontsize;  text-decoration: none; color:$menu_a_hover; background-color:$menu_a_hover_bgcolor;}
	.sm a.has-submenu { color:$menu_a_link; padding-right:32px; }

	$add_sm_default_seperation_line
	.sm a.current { background-color:$menu_current_bgcolor; color:$menu_current_color; $add_current}
	.menu_field { padding-top:$menu_padding_top; padding-left:$menu_padding_left; padding-right:$menu_padding_right; padding-bottom:$menu_padding_bottom; }
	#span_button_edit_head { $add_span_button_edit_head }
	.menu_item_a, .nav-item { text-transform:$menu_text_transform !important; }
";
	
$set_style = css_umwandeln ( $set_style );