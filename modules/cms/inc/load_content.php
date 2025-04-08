<?php
include_once (__DIR__ . "/../library/functions.php");
// include_once (__DIR__ . "/../library/function_menu.php");
include_once (__DIR__ . "/../library/phpthumb/ThumbLib.inc.php");

// $layer_content = change_resize($layer_content);
function load_content($site_id, $adminmodus = false)
{
    global $header_parallax;

    $setContent = '';

    // Wird gesetzt damit bei Bearbeitung des Popups die Felder "sortable" nicht auf der Basisseite verändert werden können
    $_SESSION['set_container_basic'] = 'set_container_basic';

    if ($_COOKIE["smart_page_id"])
        $_SESSION['smart_page_id'] = $_COOKIE["smart_page_id"];

    // include ('admin/rights.inc.php');
    $page_id = $_SESSION['smart_page_id'];
    if ($adminmodus == true) {
        // EDITIEREN DE KOPFZEILE SIND MÖGLICH

        if ($GLOBALS['right_edit_head'] or ! $GLOBALS['right_id'])
            $add_contenterditble_head = "contenteditable='true'";
        // EDITIEREN DER FUSSZEILE SIND MÖGLICH
        if ($GLOBALS['right_edit_foot'] or ! $GLOBALS['right_id'])
            $add_contenterditble_foot = "contenteditable='true'";

        if ($GLOBALS['right_edit_text'] or ! $GLOBALS['right_id'])
            $_SESSION['add_contenterditble'] = true;
    }

    /**
     * ***************************************************************************
     * Erzeugt Layer-Content
     * ***************************************************************************
     */
    // // Layer 2 PAGE
    // $sql_array[] = "
    // SELECT * FROM smart_id_layer2id_page,smart_layer,smart_langLayer
    // WHERE smart_layer.layer_id = smart_id_layer2id_page.layer_id
    // AND smart_layer.layer_id=smart_langLayer.fk_id
    // AND lang='{$_SESSION['page_lang']}'
    // AND page_id='$page_id'
    // AND set_textfield=''";

    // // Layer 2 SEITE
    // $sql_array[] = "
    // SELECT * FROM smart_id_layer2id_site,smart_layer,smart_langLayer
    // WHERE smart_layer.layer_id = smart_id_layer2id_site.layer_id
    // AND smart_layer.layer_id=smart_langLayer.fk_id
    // AND lang='{$_SESSION['page_lang']}'
    // AND smart_id_layer2id_site.site_id='$site_id'
    // AND set_textfield=''";
    // foreach ( $sql_array as $sql_query ) {
    // $mysql_query = $GLOBALS['mysqli']->query ( $sql_query ) or die ( mysqli_error ($GLOBALS['mysqli']) );
    // while ( $array = mysqli_fetch_array ( $mysql_query ) ) {
    // $layer_content = $array['text'];

    // // Verkleinern von Bildern im System + Sicherung der Bilder anlegen

    // // $layer_content = image_resizer($layer_content);

    // $layer_content = change_resize ( $layer_content );

    // // $layer_content = $array['text'];
    // $layer_y = $array['layer_y'];
    // $layer_x = $array['layer_x'];
    // $layer_h = $array['layer_h'];
    // $layer_w = $array['layer_w'];
    // $layer_id = $array['layer_id'];
    // $layer_fixed = $array['layer_fixed'];

    // $setContentLayer .= show_layer ( $layer_id, $layer_content, $array['layer_x'], $array['layer_y'], $array['layer_h'], $array['layer_w'], '', $layer_fixed );
    // }
    // }

    // check ob $_SESSION['site_id'] vorhanden ist
    $query = $GLOBALS['mysqli']->query("SELECT * from smart_langSite INNER JOIN smart_id_site2id_page ON fk_id = site_id WHERE site_id = '$site_id' ");
    $exist_site = mysqli_num_rows($query);
    $array = mysqli_fetch_array($query);
    $title = $_SESSION['site_title'] = $array['title'];
    $site_dynamic_id = $array['site_dynamic_id'];

    // Aufruf der Seite
    if ($_SESSION['new_page_id'] or ! $exist_site) {
        if ($_SESSION['new_page_id'])
            $page_id = $_SESSION['new_page_id'];
        $_SESSION['new_page_id'] = '';
        $query = $GLOBALS['mysqli']->query("SELECT * from smart_page WHERE page_id = '$page_id' ");
        $array = mysqli_fetch_array($query);
        $index_id = $array['index_id'];
        $site_id = $_SESSION['site_id'] = $index_id;
        setcookie("site_id", $_SESSION['site_id'], time() + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST']);
    }
    /*
     * Dynamischer Content wird fuer alle Felder geladen
     */

    if ($site_dynamic_id) {
        // Auslesen der Page_ID und der User_ID zum anzeigen der Bilder in den Gallerien
        $query = $GLOBALS['mysqli']->query("SELECT * FROM smart_id_site2id_page t1 INNER JOIN smart_page t2 ON t1.page_id = t2.page_id  WHERE t1.site_id = '$site_dynamic_id' ");
        $array = mysqli_fetch_array($query);

        // hier liegt für das dynamische Element Gallery der Pfad zur Gallerie des Externen Seite
        $dynamic_div_var .= "<div class='dynamic_page_id' id='{$array['page_id']}'></div>";
        $dynamic_div_var .= "<div class='dynamic_user_id' id='{$array['user_id']}'></div>";

        $GLOBALS['add_js'] .= "<script type='text/javascript' src='gadgets/dynamic/dynamic_site.js'></script>";
        $content['left'] .= "<div class='dynamic_site_left' id='$site_dynamic_id'></div>";
        $content['right'] .= "<div class='dynamic_site_right' id='$site_dynamic_id'></div>";
    }
    
    /**
     * ***************************************************************************
     * Erzeugt Content für dein Hauptbereich und Fopf/Fusszeile
     * ***************************************************************************
     */
    if ($site_id) {
        
        // Laden layout_id
        $query = $GLOBALS['mysqli']->query("SELECT smart_langSite.title from smart_langSite,smart_id_site2id_page WHERE smart_id_site2id_page.site_id='$site_id' and smart_langSite.fk_id = smart_id_site2id_page.site_id ") or die(mysqli_error($GLOBALS['mysqli']));
        $array = mysqli_fetch_array($query);
        
        /*
         * $query = $GLOBALS['mysqli']->query("SELECT content_id,content from smart_content WHERE site_id = '$site_id' and page_id = '0' ") or die (mysqli_error());
         * while ($set_array = mysqli_fetch_array($query)) {
         * $array[$set_array['content_id']] = $set_array['content'];
         * //$array[$set_array['content_id']] = show_textfield($set_array['content_id'],$set_array['content']);
         * }
         */
        // Für kopf und Fußzeile oder Elemente die auf jeder Seite gleich sind
        $query = $GLOBALS['mysqli']->query("SELECT content_id,content from smart_content WHERE page_id = '$page_id' ") or die(mysqli_error($GLOBALS['mysqli']));
        while ($set_array = mysqli_fetch_array($query)) {
            $array_content[$set_array['content_id']] = $set_array['content'];
        }
        
    } else {
        // Anzeige des Fehlers wenn Seite nicht vorhanden ist
        $setContent .= $StrNoSiteID;
        
    }

    if ($header_parallax) {
        $class_add_header = 'parallax-image ';
    }

    $setContent .= "<div class='top_phone_menu'></div>";
    $setContent .= "<div class='top_smart_content'></div>";

    $setContent .= "<div  class='smart_content' >";

    $setContent .= "<div class='{$_SESSION['set_container_basic']} smart_content_container sortable' id='header2'>";
    $setContent .= call_content('header2');
    $setContent .= "</div>";

    if ($_SESSION['admin_modus']) // show_admin_line
        $setContent .= "<div class='show_admin_line'><div style='position:absolute; left:30px; top:-28px;' class='ui label red'><span style='color:white'><i class='icon arrow up'></i>Kopfzeile</span></div></div>";

    $setContent .= "<div class='smart_content_body smart_content_body$site_id' id='smart_content_body' >";
    $setContent .= "<div class='smart_body_top'></div>";

    // ohne Border geht margin nicht
    $setContent .= "<div style='border:1px solid transparent' class='{$_SESSION['set_container_basic']} smart_content_container sortable' id='left_0'>";
    
    
    //$setContent .= "<script>call_content('$site_id'); </script>";
    
    $setContent .= call_content('', $site_id);
    
    
    $setContent .= "</div>";

    $setContent .= "<div class='smart_body_bottom'></div>";

    $setContent .= "</div>";

    if ($_SESSION['admin_modus'])
        $setContent .= "<div class='show_admin_line'><div style='position:absolute; left:30px; top:-2px;' class='ui label red'><i class='icon arrow down'></i> Fusszeile</div></div>";

    $setContent .= "<div class='{$_SESSION['set_container_basic']} smart_content_container sortable' id='footer'>";
    $setContent .= call_content('footer');
    $setContent .= "</div>";

    $setContent .= "</div>";

    $setContent .= $setContentLayer;
    $setContent .= "<div id=new_layer></div>";

    $setContent .= "</div>";

    $setContent .= "<div class='powered_by' align=center><div class='label basic ui mini'>Powered by <a href='http://www.ssi.at' target='ssi'><i>SSI</i></a></div>";

    $setContent .= "<div class='label basic ui mini'><a href='/admin'><i>Login</i></a></div>";
    $setContent .= "<br><br>";
    // "<a class=link_generate_page target='new_page' href='//center.ssi.at/register?verify_key=$verify_key'>Eine eigene Webseite erzeugen</a>";
    $setContent .= "</div>";
    $setContent .= "<div class='user_id' id = '{$_SESSION ['user_id']}'></div>";
    $setContent .= $dynamic_div_var;

    if ($adminmodus) {
        $setContent .= "<div id='{$_SESSION['site_id']}' class=site_id></div>";
        $setContent .= "<div id='{$_SESSION['company']}' class=company></div>";
    }

    $class_modalpopup = '';
    
    //important for pop-window
    $option_value = call_smart_option($_SESSION['smart_page_id'], $_SESSION['site_id']);

    $class_modalpopup .= $option_value['popup_modal_size'];
    
    if ($option_value['popup_modal_inverted'])
        $class_modalpopup .= ' inverted';

    if ($option_value['popup_modal_scrolling'])
        $class_modalpopup_content = ' scrolling';

    /* Modal für Autopopup */
    $setContent .= "
	<div class='ui modal $class_modalpopup autopopup' >
	<div class='header'>Auto-Popup (Develop)</div><div class='autopopup_content $class_modalpopup_content content'><p></p><p></p><p></p></div>
    <div class='actions'>";
    
    if ($adminmodus)
        $setContent .= "<button class='ui icon button' onclick=\"call_popup_setting()\" ><i class='icon setting'></i> Einstellungen</button>";
    if ($adminmodus)
        $setContent .= "<button class='ui orange icon button tooltip' title='Elemente auf/zuklappen' onclick='$(\".sidebar-elements\").sidebar(\"toggle\"); $(\".sidebar-popup-setting\").sidebar(\"hide\")' ><i class='icon inverted puzzle'></i> Elemente</button>";
    
    $setContent .= "<button class='ui cancel icon button'  title='Fenster schließen' ><i class='icon close'></i> Schließen</button>";
    $setContent .= "</div></div>";

    return "<div id='container_body'><div align=center>$setContent</div></div>";
    // return "<div class='ui container'>$setContent</div>";
}
