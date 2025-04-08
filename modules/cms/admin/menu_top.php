<?php
// TOUR einbinden
// include('tour_info.php');

// Ausgabe Select feld oder Einzelanzeige
// $select_domain = select_domain ( $_SESSION['user_id'], $_SESSION['smart_page_id'], 'smart' );
$select_domain = smart_select_domain($_SESSION['user_id'], $_SESSION['smart_page_id'], false, '');

$site_title = call_title($_SESSION['site_id']);

// Call all Sites
// $fav_site = favorite_sites ( $_SESSION['smart_page_id'] );
// wird nun direkt über ajax initiert function.inc.php -> favorite_sites

// $index_id = call_smart_option ( $_SESSION['smart_page_id'], '', 'index_id');

/**
 * *******************************
 * WEITERES - DROPDOWN-MENU
 * *******************************
 */
$main_menu_options .= "<a class='tooltip-left ui icon item edit_modus button_option_page'  title='Einstellen Indexseite, Google-Analytics, Facebook und weiteres' ><i class='icon world'></i>Globale Einstellungen</a>";

if ($right_edit_profile or !$right_id) {
	$main_menu_options .= "<a class='tooltip-left ui icon item edit_modus' title='Einstellen allgemeiner Schrift,Größen und Hintergrund sowie Farben und Links' onclick=\"$('.sidebar-design').sidebar('toggle')\"><i class='icon grey large paint brush'></i>Allgemeines Design</a>";
}

// Seite anlegen

// if (! $GLOBALS['hide_add_site_button']) {
// $main_menu_options .= "<a class='item' id='button_insert_share_site' ><i class='icon external share'></i>Freigegebene Seiten einhängen</a>";
// }

$main_menu_options .= "<a class='item' id='button_archive' ><i class='icon archive'></i>Archiv</a>";
// VORLAGEN BUTTON
if ($right_edit_templates or !$right_id) {
	$main_menu_options .= "<a class='item' id='button_template'><i class='icon tags'></i>Vorlage erzeugen</a>";
}

$main_menu_options .= "<div class='divider'></div>";
$main_menu_options .= "<a  class='edit_modus item tooltip-left' id='call_links' title='Nützliche Links zur Bild und Textbearbeitung'><i class='icon large tools'></i>Nützliche Links</a>";

// if ($right_set_public_page or ! $right_id) {
// $main_menu_right .= "<a class='tooltip ui icon item edit_modus' {$intro['10']} id=button_set_public title='Alles erledigt? Stelle deine Seite online!'><i class='icon green heart'></i>&nbsp;<div class='tablet'> Online stellen</div></a>";
// } else {
// $main_menu_right .= "<a class='tooltip ui icon item edit_modus' {$intro['10']} id=button_apply_for_publication title='Veröffentlichung beantragen - Hier klicken'><i class='icon green heart'></i><div class='tablet'>Veröffentlichung beantragen</div></a>";
// }

$main_menu_top_right .= "<div id='sortable_info'></div>";
$main_menu_top_right .= "<a class='item layer_save' id ='short_info_box'>Gespeichert</a>";
$main_menu_top_right .= "<div class='ui right dropdown item edit_modus'>";
$main_menu_top_right .= "Mehr<i class='dropdown icon'></i>";
$main_menu_top_right .= "<div class='menu'>$main_menu_options</div>";
$main_menu_top_right .= "</div>";
$main_menu_top_right .= "$select_domain";
// $main_menu_top_right .= "<a class='tooltip-left ui icon item button_reload' title='Seite neu laden'><i class='icon refresh'></i></a>";
// $main_menu_top_right .= "<a {$intro['8']} class='ui icon item tooltip edit_modus' id='button_allsites' title='Seitenverwaltung'><i class='icon sitemap'></i></a>";
$main_menu_top_right .= "<a class='ui tooltip icon item' title='Aus dem Smart-Kit ausloggen' href='../login/logout.php'><i class='icon large red sign out'></i></a>";

// SEITEN VERWALTEN

// if ($right_edit_allsites or ! $right_id)
// $main_menu_top_right .= "<a class='item tooltip active' id='button_allsites' title='Seitenverwaltung'><i class='icon black edit_modus list'></i>&nbsp; $site_title</a>";
// else
// $main_menu_top_right .= "<a class='item tooltip active'>&nbsp; $site_title</a>";

// PAGE OPTIONEN
// if ($right_edit_main_options or ! $right_id) {
// $main_menu_top_right .= "<a class='tooltip ui icon item edit_modus button_option_page' title='Globale Einstellungen' ><i class='icon world'></i></a>";
// }

// $main_menu = "<div class='ui small top icon borderless fixed menu admin_menu_top' style='background-color:#EEE'>";
$main_menu_top .= "<a class='item tooltip' href='../ssi_dashboard'  title='Zum Dashboard'><i class='icon large blue dashboard'></i></a>";
$main_menu_top .= "<a href='index.php' style='background-color:white' class='item tooltip-right'><div class='ui grey small header' >Smart-Kit</div></a>";
$main_menu_top .= "<a class='ui icon item button_reload tooltip' title='Inhalt neu laden'><i class='icon grey redo'></i></a>";

// $main_menu_top .= "<a class='mobile only edit_modus tooltip ui icon item' href='index.php?site_select={$_SESSION['start_site_id']}' title='Zur Startseite'><i class='icon grey home'></i></a>";
$main_menu_top .= "<a class='mobile only edit_modus tooltip ui icon item' onclick=\"CallContentSite('$index_id')\" title='Zur Startseite'><i class='icon grey home'></i></a>";
$main_menu_top .= "<a style='border-right:1px solid #ddd' class='ui icon item tooltip' id='get_favorite_title' onclick=\"CallFavorite(1)\" title=''><i title='' id='get_favorite_star' class='icon star grey'></i></a>";
$main_menu_top .= "<div class='item' id='dropdown_search_sites'></div>";
$main_menu_top .= "<a  style='border-left:1px solid #ddd' class='ui icon item button_auto_popup tooltip' title='Auto-Popup '><i class='icon grey crosshairs'></i></a>";
// $main_menu_top .= "<div class='item label ui' id='get_title'>$site_title</div>";
// $main_menu_top .= "<a class='ui tooltip icon item public_url' title='Öffentliche Webseite aufrufen' href='" . call_public_url ( $_SESSION[smart_page_id] ) . "' target='new'><i class='icon grey desktop'></i></a>";
$main_menu_top .= "<a style='border-left:1px solid #ddd' class='ui icon item get_public_url tooltip' title='Aktuelle öffentliche Seite aufrufen' href='" . call_public_url($_SESSION['smart_page_id'], $_SESSION['site_id']) . "' target='new'><i class='icon desktop green'></i></a>";
// $main_menu_top .= "<a class='ui icon item button_reload tooltip' title='Du befindest dich auf '><i class='icon grey info'></i></a>";

// todo:PHONE Modus fertig stellen
// $main_menu_top .= "<a class='ui icon item tooltip' id='view_phone_size' title='Phonedarstellung' ><i class='icon mobile alternate grey'></i></a>";

$main_menu_top .= "<div class='right icon pointing menu'>";
$main_menu_top .= $main_menu_top_right;
$main_menu_top .= "</div>";
// $main_menu .= '</div>';

/**
 * ******************************
 * LEFT -> BUTTONS (TOP)
 * ******************************
 */
// $main_menu .= "<div style='position:fixed; top:60px; left:10px; z-index:100'>";
// $main_menu .= "<a class='tooltip circular ui icon button' href='../ssi_dashboard/index.php' title='Zurück zum Dashboard'><i class='icon dashboard'></i></a>";
// $main_menu .= "</div>";

/**
 * ******************************
 * RIGHT -> BUTTONS (BOTTOM)
 * ******************************
 */

// $main_menu_right .= "<div style='position:fixed; top:60px; right:4px; z-index:100'>";

// $main_menu_right .= "<div align=center>";
// $main_menu_right .= "<a class='tooltip-left big ui icon circular button smart_edit_modus' title='Bearbeitungsmodus ein-/aus-schalten'><i id='button_lock' class='icon unlock'></i></a>";
// $main_menu_right .= "<br>";

$main_menu_right .= "<div style='height:50px'></div>";
$main_menu_right .= "<a class='tooltip-left item smart_edit_modus' title='Bearbeitungsmodus ein-/aus-schalten'><i id='button_lock' class='icon grey large unlock'></i></a>";

// WEBSEITE ONLINE STELLEN
if ($right_set_public_page or !$right_id) {
	// $main_menu_right .= "<a style='background-color:#ddd;' class='item edit_modus button-set-public' {$intro['10']}><div class='tooltip-left' title='Hier klicken um Veröffentlichung zu beginnen'><i class='icon large green cloud upload alternate'></i></div></a>";
	$main_menu_right .= "<a style='background-color:#ddd;' class='item edit_modus button-set-public' {$intro['10']}><i class='icon large green cloud upload alternate'></i></a>";
} else {
	$main_menu_right .= "<a class='tooltip-left item edit_modus' {$intro['10']} id=button_apply_for_publication title='Veröffentlichung beantragen - Hier klicken'><i class='icon green heart'></i></a>";
}

$main_menu_right .= "
<div style='width:500px' class='ui popup hidden tooltip-set-public' align=center>
<h4 class='ui header'>Änderungen veröffentlichen</h4>
<form class='ui form'>
<div class='field'><div class='ui checkbox' id='upload_hole_page'><label>Alles neu hochladen</label><input id=checkbox_upload_hole_page type='checkbox' name='upload_hole_page'></div></div>
<div class='field'><div class='ui button fluid green' id=button_set_public>Jetzt aktualisieren</div></div>
</div>
</form>
";

// Weitere Bearbeitung-Optionen (Design, Struktur,...)
// $main_menu_right .= "<div style='margin-top:10px; margin-right:3px;' class='compact icon ui big vertical buttons edit_modus'>";
// $main_menu_right .= "<a class='tooltip-left ui icon button' id='button_element_sidebar' title='Ziehe das gewünschte Element in deine Seite'><i class='icon puzzle orange'></i></a>";

// Versuch öffnen mit Fancybox (Aufruf erfolgt über include_form.php)
// $main_menu_right .= "<a data-type='iframe' href='$add_http$href' data-fancybox class='tooltip-left button_explorer item edit_modus' title='Dateien (Bilder,Pdf,mp3,..) hochladen & verwalten'><i class='icon large yellow folder open'></i></a>";

// if ($_SERVER ['HTTP_HOST'] == 'localhost')
//$main_menu_right .= "<a class='tooltip-left item edit_modus' onclick=call_finder_v1() title='Dateien (Bilder,Pdf,mp3,..) hochladen & verwalten'><i class='icon large yellow folder open'></i></a>";

$main_menu_right .= "<a class='tooltip-left item edit_modus' onclick=call_finder_v2() title='Dateien (Bilder,Pdf,mp3,..) hochladen & verwalten'><i class='icon large blue folder open'></i></a>";

// if ($right_edit_profile or ! $right_id) {
// $main_menu .= "<a class='tooltip-left ui icon button' id='button_layout_sidebar' title='Design der Seite bearbeiten'><i class='icon paint brush'></i></a>";
// }

// $main_menu_right .= "</div>";
// $main_menu_right .= "</div>";
// $main_menu_right .= "</div>";

$main_menu_right .= "<a class='edit_modus item button-elements' onclick=\"$('.sidebar-elements').sidebar('toggle')\"><i class='icon large inverted puzzle orange'></i></a>";

if (mysql_singleoutput("SELECT COUNT(*) FROM smart_id_site2id_page WHERE page_id = '{$_SESSION['smart_page_id']}' AND funnel_id "))
	$main_menu_right .= "<a class='tooltip-left item button-element-open edit_modus' title='Funnelstruktur ansehen' onclick=\"$('.sidebar-funnel').sidebar('toggle')\"><i class='icon orange large amazon'></i></a>";

// $main_menu .= "<a class='tooltip-left button-option-open edit_modus' title='Seiteneinstellungen öffnen' onclick=call_option('{$_SESSION['site_id']}')><i class='icon large setting'></i></a>";

// $main_menu_right .= "<a style='position:fixed; bottom:160px; right:26px;' title='test' class='edit_modus button-elements circular ui orange huge icon button' onclick=\"$('.sidebar-elements').sidebar('toggle')\"><i class='icon puzzle'></i></a>";

if ($right_edit_menu or !$right_id) {
	$main_menu_right .= "<a href=# class='tooltip-left item edit_modus' title='Fenster - Seiten und Menüstruktur bearbeiten' onclick=editMenuStructure()><i class='icon grey large sitemap'></i></a>";
	$main_menu_right .= "<a href=# class='tooltip-left item edit_modus' title='Sitebar - Seiten und Menüstruktur bearbeiten'  onclick=\"$('.sidebar-menu').sidebar('toggle')\"><i class='icon grey large list'></i></a>";
}

$main_menu_right .= "<a href=# class='tooltip-left item edit_modus' id='button_option_site' title='Seiten-Einstellungen (Titel, Metatexte, ..)'><i class='icon grey large setting'></i></a>";

$main_menu_right .= "<hr class='edit_modus'>";

if (!$GLOBALS['hide_add_site_button']) {
	// $main_menu_right .= "<a style='position:fixed; bottom:130px; right:25px;' class='edit_modus tooltip-left circular ui green huge icon button' onclick=addNewSite() title='Neue Seite anlegen'><i class='icon plus'></i></a>";
	$main_menu_right .= "<a class='edit_modus item tooltip-left' onclick=addNewSite() title='Neue Seite anlegen'><i class='icon green large plus'></i></a>";
}
// $main_menu_right .= "<br><a style='position:fixed; bottom:70px; right:25px;' class='edit_modus tooltip-left circular grey ui huge icon button' onclick=cloneSite('{$_SESSION['site_id']}') title='Seite klonen'><i class='icon clone'></i></a>";
$main_menu_right .= "<a  class='edit_modus item tooltip-left' onclick=cloneSite('{$_SESSION['site_id']}') title='Seite klonen'><i class='icon grey large clone'></i></a>";
$main_menu_right .= "<hr class='edit_modus'>";

// if ($right_edit_profile or ! $right_id) {
// $main_menu_right .= "<a class='tooltip-left item button-designer-open edit_modus' title='Designer anzeigen' onclick=\"$('.sidebar-design').sidebar('toggle')\"><i class='icon grey large paint brush'></i></a>";
// }

/**
 * ***********************************************
 * Element-Speedbar
 * **********************************************
 */
$main_menu_right .= "
<div class='ui popup hidden tooltip-elements'>
<div class='ui secondary compact vertical labeled icon mini menu'>
<div id='splitter' class='new_module item tooltip-left' title='Splitter hineinziehen' style='cursor:move;'><i class='columns icon'></i>Splitter</div>
<div id='textfield' class='new_module item tooltip-left tooltip' title='Textfeld hineinziehen' style='cursor:move;'><i class='content icon'></i>Text</div>
<div id='button' class='new_module item tooltip-left' title='Button hineinziehen' style='cursor:move;'><i class='ellipsis horizontal icon'></i>Button</div>
<div id='photo' class='new_module item tooltip-left' title='Photo hineinziehen' style='cursor:move;'><i class='photo layout icon'></i>Photo</div>
<div id='gallery' class='new_module item tooltip-left' title='Galerie hineinziehen' style='cursor:move;'><i class='file image outline icon'></i>Galerie</div>
<div id='embed' class='new_module item tooltip-left' title='Video und IFrames hineinziehen' style='cursor:move;'><i class='youtube play icon'></i>Video</div>
<a onclick=\"$('.sidebar-elements').sidebar('toggle')\" class='item tooltip-left' title='Hier klicken'><i class='ellipsis horizontal icon'></i>Mehr</a>
</div></div>";

$main_menu .= "<div style='background-color:#EEE; z-index:101;' class='ui borderless icon vertical right fixed  menu sidebar-right'>$main_menu_right</div>";
$main_menu .= "<div style='background-color:#EEE; z-index:102;' class='ui borderless icon top fixed menu sidebar-top'>$main_menu_top</div>";

//$main_menu .= "<div id='{$_SESSION['site_id']}' class=site_id></div>";
//$main_menu  .= "<div style='border:1px solid red; position:fixed; right:0px; width:60px; height:100%; background-color:#EEE'></div>";