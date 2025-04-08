<?php
/**
 * ********************************************************************************************************
 * Load - ADMIN - MODUS
 * *********************************************************************************************************
 */


// laden der Class fur die Ausgabe der Maske inkl. Content
include_once(__DIR__ . '/ssiPlattform.php');

// Testmodus
$set_autopopup = 'off';

$form = new ssiPlattform("smartkit", "Smart"); // modulname, Überschrift
$form->setConfig("version", $_SESSION['version_smart']);
$form->setConfig("hideMainMenu", true);
$form->setConfig("hideVersionBorder", true);



// Aufruf nur wenn Login korrekt war
if ($form->login() == true) {
	$_SESSION['admin_modus'] = true;

	include_once(__DIR__ . '/admin/include.inc.php');

	include_once(__DIR__ . '/config.inc.php');

	$setContent['main'] .= "<div style='display: none' class='hideAll'>" . $main_menu . $content_admin . "</div>";
}

if ($_GET['preview'])
	$_SESSION['admin_modus'] = true;

if (!$check_page_id) {
	$form->setContent("text", $StrNoPageID);
	echo $form->getHTML();
	return;
}

// include_once (__DIR__ . '/library/css_umwandler.inc');
include(__DIR__ . "/inc/load_css.php");
include(__DIR__ . "/inc/load_content.php");

// check ob seite zu page gehört
$query = $GLOBALS['mysqli']->query("SELECT * FROM smart_id_site2id_page WHERE site_id = '{$_SESSION['site_id']}' AND page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
$count_sites = mysqli_num_rows($query);
if ($count_sites)
	$site_id = $_SESSION['site_id'];
else {
	$site_id = $_SESSION['site_id'] = '';
}

// Clonetester
// clone_layer_splitter(16637); exit();

// setcookie ( "site_id", $_SESSION['site_id'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );
if ($set_error) {

	$form->setContent("text", "<div align=center><div class='message ui error compact'>$error_message</div></div>");
} else {

	$form->setContent("sidebar", $content_sidebar_admin);
	$form->setContent("text", $setContent['main'] . $facebook_plugin . load_content($_SESSION['site_id'], true));
}

// $add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/fancybox3/jquery.fancybox.css'>";
$add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/fleximages/jquery.flex-images.css'>";

$add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/carousel/assets/owl.carousel.min.css'>";
$add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/gallery/carousel/assets/owl.theme.default.min.css'>";

// $add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/mobilenav/dist/hc-offcanvas-nav.css'>";

/**
 * ********SMART-MENU old
 */
// $add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/sm-core-css.css' />";

$add_css .= "\n<link rel='stylesheet' type='text/css' href='js/scrollup/css/themes/$scrollup_style.css'>";

if ($set_output['css_google'])
	$add_css .= $set_output['css_google'];
else
	$add_css .= "\n<link rel=\"stylesheet\" id='css_google'>";

$add_css .= "\n<link rel='stylesheet' type='text/css' href='css/style_first.css'>";

// $add_css .= "\n<link rel='stylesheet' type='text/css' href='gadgets/menu/smartmenus/css/$select_menu_layout/$select_menu_layout.css' id='id_menu_layout' />";

// $add_css .= "\n<link rel='stylesheet' type='text/css' href='admin/js/jstree/dist/themes/default/style.min.css' />";
$add_css .= "\n<link rel='stylesheet' type='text/css' href='../vendor/vakata/jstree/dist/themes/default/style.min.css' />";
$add_css .= "\n<link rel='stylesheet' type='text/css' href='admin/css/style.css' />";
$add_css .= $GLOBALS['add_css2'];
$add_css .= "\n<style type='text/css' id='set_style'>$set_style</style>";
$add_css .= "\n<link rel='stylesheet' type='text/css' href='css/style_second.css'>";
// $add_css .= "\n<link href='js/previewer/dist/previewer.css' rel='stylesheet'>";
$add_css .= "\n<style type='text/css' id='edit_layout'></style>";

$add_js .= "\n<script type='text/javascript' src='js/scrollup/jquery.scrollUp.min.js'></script>";
$add_js .= "\n<script type='text/javascript' src='js/paroller/dist/jquery.paroller.min.js'></script>";
$add_js .= "\n<script src='https://cdn.jsdelivr.net/npm/simple-parallax-js@5.1.0/dist/simpleParallax.min.js'></script>"; // Parallax-Effekt für ein Photo
$add_js .= "\n<script type='text/javascript' src='gadgets/gallery/fleximages/jquery.flex-images.min.js'></script>";
$add_js .= "\n<script type='text/javascript' src='gadgets/gallery/carousel/owl.carousel.min.js'></script>";
$add_js .= "\n<script type='text/javascript' src='smart_form/TouchSwipe/jquery.touchSwipe.js'></script>";
$add_js .= "\n<script type='text/javascript' src='gadgets/marquee/jquery.marquee.min.js'></script>";
$add_js .= "\n<script type='text/javascript' src='js/smart.js'></script>";
$add_js .= "\n<script type=\"text/javascript\" src=\"admin/js/smart_admin.js\"></script>";
$add_js .= "\n<script type=\"text/javascript\" src=\"../vendor/vakata/jstree/dist/jstree.min.js\"></script>";
$add_js .= "\n<script type=\"text/javascript\" src=\"admin/js/textfield.js\"></script>";
$add_js .= "\n<script type=\"text/javascript\" src=\"admin/js/form_gadget.js\"></script>";
$add_js .= "\n<script type=\"text/javascript\" src=\"admin/js/form_autopopup.js\"></script>";
$add_js .= $GLOBALS['add_path_js'];
$GLOBALS['add_js2'] = preg_replace("[<script>|</script>]", "", $GLOBALS['add_js2']);
$add_js .= "\n<script type='text/javascript'>" . $GLOBALS['add_js2'] . "</script>";

if ($set_autopopup == 'on')
	$add_js .= "\n<script type='text/javascript'>$(document).ready( function() { open_autopopup('$site_id'); }) </script>";

$form->setCss($add_css);
$form->setJs($add_js);

echo $form->getHTML();