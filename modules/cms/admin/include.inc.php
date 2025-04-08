<?php
$StrNoPageID .= "<a href='../index.php'><< Zur&uuml;ck</a><hr><div align=center>Die Page scheint nicht zu existieren, da keine Page_ID vorhanden ist!</div><hr>";
$StrNoSiteID .= "<hr><div align=center>Die Seite zur Page scheint nicht zu existieren, da keine Site_ID vorhanden ist!</div><hr>";

// Check ob Page existiert
if ($_POST['page_select'] or $_GET['page_select']) {

	if ($_POST['page_select']) {
		$_COOKIE["smart_page_id"] = $_SESSION['smart_page_id'] = $_POST['page_select'];
		setcookie("smart_page_id", $_POST['page_select'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
	} elseif ($_GET['page_select']) {
		$_COOKIE["smart_page_id"] = $_SESSION['smart_page_id'] = $_GET['page_select'];
		setcookie("smart_page_id", $_GET['page_select'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
	}

	if ($_SESSION['superuser_id']) {
		// User ID-auslesen - aber nur wenn Superuser eingeloggt ist
		$query = $GLOBALS['mysqli']->query("SELECT user_id FROM smart_page WHERE page_id ='{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
		$array = mysqli_fetch_array($query);
		$_SESSION['user_id'] = $array['user_id'];
		setcookie("user_id", $_SESSION['user_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
	}

	// Prüfen ob site_id in page_id vorhanden ist
	$query = $GLOBALS['mysqli']->query("SELECT * FROM smart_id_site2id_page WHERE page_id = '{$_SESSION['smart_page_id']}' AND site_id = '{$_SESSION['site_id']}' ") or die('db: smart_id_site2id_page<br>' . mysqli_error($GLOBALS['mysqli']));
	if (!mysqli_num_rows($query)) {
		// site_id löschen

		$_SESSION['site_id'] = '';
	}
}

if ($_COOKIE["smart_page_id"] or $_SESSION["smart_page_id"]) {

	if ($_COOKIE["smart_page_id"] and !$_SESSION['smart_page_id'])
		$_SESSION['smart_page_id'] = $_COOKIE["smart_page_id"];

	if ($_GET['site_select']) {
		$_SESSION['site_id'] = $_GET['site_select'];
		setcookie("site_id", $_SESSION['site_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
	}

	// if ($_COOKIE["site_id"] and ! $_SESSION['site_id']) {
	// $_SESSION['site_id'] = $_COOKIE["site_id"];
	// }

	$smart_page_id = $GLOBALS['mysqli']->real_escape_string($_SESSION['site_id']);
}

include (__DIR__ . '/rights.inc.php');

// Prüfen ob die Page im System vorhanden ist
$query = $GLOBALS['mysqli']->query("SELECT page_id FROM smart_page where page_id = '{$_SESSION['smart_page_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
$array = mysqli_fetch_array($query);
if ($array['page_id'])
	$check_page_id = true;

// Globale Optionen aufrufen
call_smart_option($_SESSION['smart_page_id'], '', '', true);
$_SESSION['site_key'] = $site_key;
$_SESSION['secret_key'] = $secret_key; // Recaptcha

if ($_POST['page_select'] or $_GET['page_select']) {
	$_SESSION['site_id'] = $index_id; // fix site_id von Index verwenden wenn Seite neu geladen wird
	setcookie("site_id", $_SESSION['site_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
} elseif (!$_SESSION['site_id']) {
	$_SESSION['site_id'] = $index_id; // Start ID wird gewählt wenn keine "site_id" vorhanden ist
	setcookie("site_id", $_SESSION['site_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
}

// Prüft ob eine Webseite im System vorhanden ist und ruft die erste Seite au auf
if ($check_page_id == false) {
	$query = $GLOBALS['mysqli']->query("SELECT page_id FROM smart_page where user_id = '{$_SESSION['user_id']}' ") or die(mysqli_error($GLOBALS['mysqli']));
	$array = mysqli_fetch_array($query); {
		$_SESSION['smart_page_id'] = $array['page_id'];
		call_smart_option($_SESSION['smart_page_id'], '', '', true);
		$_SESSION['site_key'] = $site_key;
		$_SESSION['secret_key'] = $secret_key; // Recaptcha
		$check_page_id = true;
	}
}

// Laden von der Menüleiste oben
include ('menu_top.php');

/**
 * *********************************************************************************************
 * SIDEMAP
 * ********************************************************************************************
 */

include ('inc/sidebar_element.php');

// include ('inc/sidebar_funnel.php');
include ('ajax/form_design.php');

// SIDEBAR - OPTIONEN FÜR DAS JEWEILIGE ELEMENT
$content_sidebar_admin .= "
<div  style='z-index:1999; background-color:#d4fb78; display: none; width:280px;' class='hideAll ui right sidebar segment sidebar-element-setting'>
<a style='right: 278px; top: 0px' class='button-element-setting-close' title='Elementebar schließen' onclick=\"$('.sidebar-element-setting').sidebar('toggle')\"><i class='icon large angle right'></i></a>
<div id=sidebar-element-setting-content class='sitebar_container' style='overflow:auto'></div>
</div>";

// SIDEBAR - SIDEMAP
$content_sidebar_admin .= "
<div style='z-index:2000; display: none; width:260px;' class='hideAll ui segment right sidebar sidebar-menu'>
<a style='right: 258px; top: 284px' class='button-sitemap-close tooltip-left' title='Sidemap schließen' onclick=\"$('.sidebar-menu').sidebar('toggle')\"><i class='icon large list'></i></a>
<div id='sidebar-content-structure-menu' class='sitebar_container' style='overflow:auto'></div>
</div>";

// SIDEBAR - FUNNEL
$content_sidebar_admin .= "
<div style='z-index:2000; display: none;' class='hideAll ui segment right sidebar sidebar-funnel'>
<a class='button-funnel-close tooltip-left' title='Funnels schließen' onclick=\"$('.sidebar-funnel').sidebar('toggle')\"><i class='icon orange large amazon'></i></a>
<div id='sidebar-content-structure-funnel' class='sitebar_container' style='overflow:auto'></div>
</div>";

// SIDEBAR - POPUP-Setting
$content_sidebar_admin .= "
<div style='background-color:#d4fb78; z-index:1002; display: none; width:280px;' class='hideAll ui right sidebar segment sidebar-popup-setting'>
<a style='right: 278px; top: 0px' class='button-popup-setting-close' title='Elementebar schließen' onclick=\"$('.sidebar-popup-setting').sidebar('toggle')\"><i class='icon large angle right'></i></a>
<div id=sidebar-popup-setting-content class='sitebar_container' style='overflow:auto'></div>
</div>";

/**
 * *********************************************************************************************
 * MODAL & Flypout
 * ********************************************************************************************
 */


$content_admin .= "
<div class='ui modal modal_phone_version'><i class='close icon'></i><div class='content'><div align=center class='preview_phone_div'><iframe class='preview_phone_frame' frameborder='0'></iframe></div></div></div>
<div class='ui modal modal_version'><i class='close icon'></i><div class='scrolling content'></div></div>
<div class='ui modal form-template' ><i class='close icon'></i><div class='header'>Vorlage bearbeiten</div><div class='content'></div></div>
<div class='ui modal large allsites' id ='allsites' ><div class='header'>Alle Seiten</div><div class='scrolling content'></div><div class='actions'><div class='ui cancel button'>Schließen</div></div></div>
<div class='ui modal list_archive' ><div class='header'>Archivierte Elemente</div><div class='content'></div><div class='actions'><div class='ui cancel button'>Schließen</div></div></div>
<div class='ui modal' id='option_site' ><div class='header'>Seiten-Einstellungen</div><i class='close icon'></i><div class='content'></div></div>
<div class='ui modal' id='show_option'><i class='close icon'></i><div class='content' id=show_option_content></div></div>
<div class='ui modal modal-edit-menu'><i class='close icon'></i><div class='header'>Seitenstruktur bearbeiten</div><div class='content'></div><div class='actions'><div class='ui cancel button'>Schließen</div></div></div>
<div class='ui modal small' id='modal_small'><i class='close icon'></i><div class='content' id='modal_small_content'></div></div>
<div class='ui modal small load'><div class='content' id=modal_window></div><div class='actions'><div class='ui cancel button load'>Schließen</div></div></div>
<div class='ui modal large' id='edit_explorer'><i class='close icon'></i><div class='content'></div></div>
<div class='ui modal amazon'><i class='close icon'></i><div class='scrolling content' id='modal_amazon_content'></div><div class='actions'><div class='ui cancel button' >Schließen</div></div></div>
<div class='ui modal' id='modal_edit_formular'><div class='header'>Feld bearbeiten</div><div class='content'><p></p><p></p><p></p></div></div>
<div class='ui modal' id='modal_call_links'><div class='header'>Usefull Links</div><div class='content'><p></p><p></p><p></p></div></div>
<div class='ui modal fullscreen' id='show_explorer'><div class='content' id=show_explorer_content></div><div class='actions'><div class='ui cancel button'>Schließen</div></div></div>
";

$content_admin .= "<input type='hidden' id='hiddenVariable' value=''>";
$content_admin .= "<input type='hidden' id='index_id' value='$index_id'>";
$content_admin .= "<a href=# style='position:fixed; bottom:20px; left:20px;' title='Was gibt es neues' class='tooltip-left' id='button_versiontext'><div class='label basic mini ui'>Version {$_SESSION['version_smart']}</div></a>";


//s-config ist im admin/js/smart_admin.js zu finden
$arr['option_global'] = array('title' => 'Allgemeine Einstellungen', 'content' => 'test', 'class' => 'right', 'z-index:9999');
$content_admin .= generate_element($arr, 'flyout');


//js-config ist in smart_form/smart_form.js zu finden
$modul_arr['modul_finder']['title'] = 'Finder';
$modul_arr['modul_finder']['class'] = 'fullscreen scrolling';
$modul_arr['modul_finder']['zindex'] = '2001';
//$modul_arr ['modul_finder'] ['content'] = "<iframe src='smart_form/file_manager.php' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframeFlyout(this)'></iframe>";
$modul_arr['modul_finder']['content'] = "<iframe src='../ssi_finder/' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframe(this)'></iframe>";
$content_admin .= generate_element($modul_arr, 'flyout');

$modul_arr['flyout_finder']['title'] = 'Finder (v2)';
$modul_arr['flyout_finder']['class'] = 'fullscreen scrolling';
$modul_arr['flyout_finder']['zindex'] = '2001';
$modul_arr['flyout_finder']['content'] = "<iframe src='smart_form/file_manager.php' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframeFlyout(this)'></iframe>";
$content_admin .= generate_element($modul_arr, 'flyout');


//<div class='ui active inverted dimmer'  id='call-loader'><div class='ui text loader'>Inhalt wird geladen</div></div>
