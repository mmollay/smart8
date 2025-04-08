<?php
include_once (__DIR__ . '/css.php');
include_once (__DIR__ . '/../../library/function_menu.php');

// Prüft ob Menü angezeigt werden soll
$check_query = $GLOBALS['mysqli']->query("SELECT menubar_disable from smart_id_site2id_page WHERE site_id='{$_SESSION['site_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
$check_array = mysqli_fetch_array($check_query);

if ($check_array['menubar_disable'])
    return;

if (! $menu_text_transform)
    $menu_text_transform = 'none';

// $add_css2 .= "\n<style>.menu_item_a { text-transform:$menu_text_transform !important; }</style>";
// $add_css2 .= "\n<style type='text/css' id='set_style_menu'>$set_style</style>";

if (! $GLOBALS['menu_hidden']) {

    if (! $GLOBALS['load_element_menu']) {
        // Wird auf der Indexseite geladen
        // $add_css2 .= "\n<style type='text/css'>@import 'gadgets/menu/mobilenav/dist/hc-offcanvas-nav.css'; </style>";
        // $add_path_js .= "\n<script type='text/javascript' src='gadgets/menu/mobilenav/dist/hc-offcanvas-nav.js'></script>";
    }
    $GLOBALS['load_element_menu'] = TRUE;

    if ($menu_version != 'semantic') {
        if (! $GLOBALS['load_element_menu_classic']) {
            // $add_css2 .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/sm-core-css.css' />";
            // $add_css2 .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/sm-default/sm-default.css' id='id_menu_layout' />";

            $add_path_js .= "\n<script type='text/javascript' src='gadgets/menu/smartmenus/jquery.smartmenus.min.js'></script>";
            // $add_path_js .= appendScript('gadgets/menu/smartmenus/jquery.smartmenus.min.js');
            $GLOBALS['load_element_menu_classic'] = TRUE;
        }
    }

    $menuData = generateMenuStructure($_SESSION['smart_page_id']);

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
            $set_menu_class .= 'inverted';

        if ($menu_inverted && (! $menu_color or $menu_color == 'tranparent'))
            $add_style_semantic_menu = "style='border-width: 0px;'";

        if ($menu_stretch) {
            // automatisches ermitteln der Anzahl der Felder in erster Ebene
            $menu_count_item = count($menuData[parents][0]) + $menu_count_item;

            // Anzahl der ersten Ebene ermitteln
            $set_menu_class .= convertNumberToWord($menu_count_item) . ' item';
        }

        // $output .= "<div align='left'><pre>".htmlspecialchars("<div class='ui $menu_color $menu_size secondary pointing menu'>" . buildMenuSemantic ( 0, $menuData, rand(5, 15) ) . "\n</div>")."</pre></div> ";
        $output_menu = "<div class='ui $menu_color $menu_size $menu_design $set_menu_class menu' $add_style_semantic_menu>" . buildMenuSemantic(0, $menuData) . "</div>";
        $output_js .= "$('.menu_dropdown').dropdown({  on: 'hover' });";
    } elseif ($menu_version == 'classic') {
        // Klassisches Menü

        // if ($_SESSION['show_loginbar']) {
        // if ($set_ajax)
        // include (__DIR__ . '/../../gadgets/login_bar/include.inc.php');
        // else
        // include (__DIR__ . '/../../gadgets/login_bar/include.inc.php');
        // $login_bar = "<div style='float:right;' id='div_login_bar'>$output</div>";
        // }

        if ($version != 'sidebar') {
            if (! $version)
                $version = "sm-default";

            // Menubar - wird bei mobil verkleinert dargestellt
            // $add_class = 'mobile';
        }

        if ($_SESSION['loginbar_color'])
            $color = $_SESSION['loginbar_color'];

        // <!-- Mobile menu toggle button (hamburger/x icon) -->
        // $output_menu .= "<input id='main-menu-state' type='checkbox' /><label class='main-menu-btn' for='main-menu-state'><span class='main-menu-btn-icon main_menu_field'></span>$menu_logo</label>";
        $output_menu .= buildMenu(0, $menuData, $version);
        if ($login_bar)
            $output_menu .= "$login_bar";
        $output_js .= "$('#main-menu').smartmenus();";
    }

    /**
     * ********************************************************************
     * Darstellung Smart-Phone Navigation
     * *******************************************************************
     */

    $array_menu_logo_src = call_smart_option($_SESSION['smart_page_id'], '', array(
        'menu_logo'
    ), true);
    $menu_logo_src = $array_menu_logo_src['menu_logo'];

    if ($menu_logo_src)
        $menu_logo = "<img height='100%' src='$menu_logo_src'>";

    if ($phone_nav_on_top) {
        $pos_phone_nav = 'position:fixed; z-index:1000;';
        if ($_SESSION['admin_modus']) {
            $pos_phone_nav .= 'top:44px; left:0px; right:61px; ';
        } else
            $pos_phone_nav .= 'top:0px; left:0px; right:0px; ';
    } else {
        $pos_phone_nav .= 'position:sticky; top:0;';
    }

    $output .= "<div style='background-color:$phone_nav_bg_color; height:60px; padding:1px; $pos_phone_nav' class='phone-nav'>";
    $output .= "<a id='phone-toggle$id'><div style='padding-left:10px; color:$phone_nav_font_color;'><i class='icon big bars'></i></div></a>";
    $output .= "<nav style='display:none' id='phone-nav$id'>" . buildMenuUl(0, $menuData) . "</nav>";
    $output .= "<div style='float:right; height:99%'><a href=# onclick=\"CallContentSite('$index_id')\" >$menu_logo</div>";
    $output .= "</div>";

    $output_js .= "
		$('#phone-nav$id').hcOffcanvasNav({ customToggle: $('#phone-toggle'+$id), maxWidth: false,
			/*navTitle: 'All Categories',*/
            levelTitles: true,
			// pushContent: '#container',
            insertClose: 1,
            closeLevels: false
		});";

    $output .= "<div class='menu_field' id='$id'>" . $output_menu . "</div>";
    $output .= "\n<style type='text/css' id='set_style_menu'>$set_style</style>";
}

$add_js2 .= "
$(document).ready(function() {
$output_js
})
";
