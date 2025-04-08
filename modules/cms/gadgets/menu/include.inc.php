<?php
include_once (__DIR__ . '/css.php');
include_once (__DIR__ . '/../../library/function_menu.php');

// Prüft ob Menü angezeigt werden soll
$check_query = $GLOBALS ['mysqli']->query ( "SELECT menubar_disable from smart_id_site2id_page WHERE site_id='{$_SESSION['site_id']}'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
$check_array = mysqli_fetch_array ( $check_query );

if ($check_array ['menubar_disable'])
	return;

if ($GLOBALS ['menu_hidden'])
	return;

if (! $menu_text_transform)
	$menu_text_transform = 'none';

$menuData = generateMenuStructure ( $_SESSION ['smart_page_id'] );

if (! $menu_version)
	$menu_version = 'semantic';

// Alternative - Menu
if ($menu_version == 'semantic') {
	$set_menu_class = '';
	if ($menu_vertical)
		$set_menu_class .= 'vertical ';

	if ($menu_fixed)
		$set_menu_class .= 'fixed ';

	if ($menu_fluid)
		$set_menu_class .= 'fluid ';

	if ($menu_compact)
		$set_menu_class .= 'compact ';

	if ($menu_borderless)
		$set_menu_class .= 'borderless ';

	if ($menu_inverted)
		$set_menu_class .= "inverted $menu_color";

	if ($menu_attached)
		$set_menu_class .= 'top attached ';

	if ($menu_inverted && (! $menu_color or $menu_color == 'tranparent'))
		$add_style_semantic_menu = "style='border-width: 0px;'";

	if ($menu_stretch) {
		// automatisches ermitteln der Anzahl der Felder in erster Ebene
		$menu_count_item = count ( $menuData ['parents'] [0] ) + $menu_count_item;

		// Anzahl der ersten Ebene ermitteln
		$set_menu_class .= convertNumberToWord ( $menu_count_item ) . ' item';
	}

	if ($menu_color2)
		$menu_color = $menu_color2;

	// $output .= "<div align='left'><pre>".htmlspecialchars("<div class='ui $menu_color $menu_size secondary pointing menu'>" . buildMenuSemantic ( 0, $menuData, rand(5, 15) ) . "\n</div>")."</pre></div> ";
	$output .= "\n<script>$(document).ready(function() { $('.menu_dropdown').dropdown({  on: 'hover' }); });</script>";
	$output .= "<div class='menu_field' id='$id'><div class='ui $menu_size $menu_design $set_menu_class menu' $add_style_semantic_menu>" . buildMenuSemantic ( 0, $menuData, $menu_color ) . "</div></div>";

	if ($menu_color_all)
		$output .= "<style>.menu_item_a { color: $menu_color !important; } </style>";
	else
		$output .= "<style>.menu_item_a.active { color: $menu_color !important; } </style>";
} 

elseif ($menu_version == 'classic') {
	// Smart_Menu
	$output .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/sm-core-css.css' />";
	$output .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/sm-default/sm-default.css' id='id_menu_layout' />";
	$output .= "\n<div class='menu_field' id='$id'>" . buildMenu ( 0, $menuData, 'sm-default' ) . "</div>";
	$output .= "\n<script type='text/javascript' src='gadgets/menu/smartmenus/jquery.smartmenus.min.js'></script>";
	$output .= "\n<script>$(document).ready(function() { $('#main-menu').smartmenus({showFunction: function(ul, complete) { ul.fadeIn(250, complete); } }); }); </script>";
}

/**
 * ********************************************************************
 * Darstellung Smart-Phone Navigation
 * *******************************************************************
 */
$array_menu_logo_src = call_smart_option ( $_SESSION ['smart_page_id'], '', array ('menu_logo' ), true );
$menu_logo_src = $array_menu_logo_src ['menu_logo'];

if ($menu_logo_src)
	$menu_logo = "<img height='100%' src='$menu_logo_src'>";

if ($phone_nav_on_top) {
	$pos_phone_nav = 'position:fixed; z-index:1000;';
	if ($_SESSION ['admin_modus']) {
		$pos_phone_nav .= 'top:44px; left:0px; right:61px; ';
	} else
		$pos_phone_nav .= 'top:0px; left:0px; right:0px; ';
} else {
	$pos_phone_nav .= 'position:sticky; top:0;';
}
if (! $phone_nav_bg_color)
	$phone_nav_bg_color = 'white';

$output .= "\n<style type='text/css' id='set_style_menu'>$set_style</style>";
// $output .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/mobilenav/dist/hc-offcanvas-nav.css'>";
$output .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/mobilenav/dist/hc-offcanvas-nav-grey.css'>";
$output .= "\n<script type='text/javascript' src='gadgets/menu/mobilenav/dist/hc-offcanvas-nav.js'></script>";
$output .= "\n<script>$(document).ready(function() { $('#phone-nav$id').hcOffcanvasNav({ customToggle: $('#phone-toggle'+$id), maxWidth: false, labelBack:'<i class=\"icon arrow left\"></i>' , labelClose: '<i class=\"icon close\"></i>' }); })</script>";

$output .= "<div style='background-color:$phone_nav_bg_color; height:60px; padding:1px; $pos_phone_nav' class='phone-nav'>";
$output .= "<a id='phone-toggle$id'><div style='padding-left:10px; color:$phone_nav_font_color;'><i class='icon big bars'></i></div></a>";
$output .= "<nav style='display:none' id='phone-nav$id'>" . buildMenuUl ( 0, $menuData ) . "</nav>";
if ($menu_logo)
	$output .= "<div style='float:right; height:99%'><a href=# onclick=\"CallContentSite('$index_id')\" >$menu_logo</a></div>";
$output .= "</div>";
				