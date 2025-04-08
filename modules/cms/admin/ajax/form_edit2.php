<?php

// Zugangsdaten fuer die Datenbank
include_once (__DIR__ . '/../../../login/config_main.inc.php');

foreach ($_POST as $key => $value) {
    if ($value) {
        $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
    }
}
$list_id = $_POST['list_id'];

switch ($_POST['list_id']) {

    case 'global_option':

        $timestamp = date('Y-m-d H:i:s');
        $array_option_fields = array(
            'index_id' => "$index_id",
            'TrackingCode' => "$TrackingCode",
            'TrackingCodeV4' => "$TrackingCodeV4",
            'GoogleAds' => "$GoogleAds",
            'OptimizeCode' => "$OptimizeCode",
            'FacebookPixel' => "$FacebookPixel",'appID' => "$appID",'index_off' => "$index_off",'appSecret' => "$appSecret",'update_date' => $timestamp,'no_smartphone_modus' => "$no_smartphone_modus",'generate_dirpath' => "$generate_dirpath",'menu_logo' => "$menu_logo",'site_key' => "$site_key",'secret_key' => "$secret_key",'cookie_text' => "$cookie_text",'cookie_consent' => "$cookie_consent",'cookie_button_color' => "$cookie_button_color",'menu_disable' => "$menu_disable",
            'global_set_dynamic' => "$global_set_dynamic",
            'GoogleTagManager' => "$GoogleTagManager"
        );
        save_smart_option($array_option_fields, $_SESSION['smart_page_id']);
        set_update_site('all');
        echo "ok";
        break;

    /**
     * *******************************************************************************
     * GROUP - FORM 2
     * *******************************************************************************
     */
    case 'site_list':
    case 'site_form':

        // 'body_backgroundimage_site',
        // 'header_backgroundimage_site',
        $new_array = array('set_dynamic' => $set_dynamic,'no_index' => $no_index,'body_backgroundimage_site' => $body_backgroundimage_site,'header_backgroundimage_site' => $header_backgroundimage_site);

        // echo $menu_disable;

        // WENN DIE SEITE GECLONT WIRD, WIRD update_id zurück gesetzt
        if ($_POST['clone_id'])
            $_POST['update_id'] = '';

        $site_id = $_POST['update_id'];
        $page_id = $_SESSION['smart_page_id'];
        $page_lang = $_SESSION['page_lang'];

        if ($_POST['clone_id']) {
            // Auslesen der aktuelle Position und ob es eine parent_id gibt
            $sql = "Select parent_id, position FROM smart_id_site2id_page WHERE site_id = {$_POST['clone_id']} ";
            $query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
            $clone_array = mysqli_fetch_array($query);
            $clone_parent_id = $clone_array['parent_id'];
            $clone_position = $clone_array['position'] + 1;
        }
        else {
            $clone_parent_id = 0;
            $clone_position = 0;
        }
        
        if (! $site_id or $_POST['clone_id']) {

            // Speichert Seite (Wird benötigt fuer das erzeugen der Vorlage)!!!!
            $GLOBALS['mysqli']->query("INSERT INTO smart_site SET  matchcode = '$matchcode' , parent_id = 0, position = 0 ") or die(mysqli_error($GLOBALS['mysqli']));

            $site_id = mysqli_insert_id($GLOBALS['mysqli']);

            // if ($split_representation)
            // $split_representation = 'single';

            // Speichert Verknuepfung zur Page und Profil
            $sql = "INSERT INTO smart_id_site2id_page SET
			site_id   = '$site_id',
            uuid  = UUID(),
			page_id   = '$page_id',
			site_dynamic_id = " . (int) $site_dynamic_id . ",
			layout_id = " . (int) $profil_id . ",
			menu_disable = " . (int) $menu_disable . ",
            menu_newpage = " . (int) $menu_newpage . ",
			menubar_disable = " . (int) $menubar_disable . ",
			breadcrumb_disable =" . (int) $breadcrumb_disable . ",
            funnel_id =" . (int) $funnel_id . ",
            funnel_short =" . (int) $funnel_short . ", 
			split_representation = '$split_representation',
			dynamic_site = " . (int) $dynamic_site . ",
			dynamic_name = '$dynamic_name',
			layout_array = '$array_new',
			parent_id = " . (int) $clone_parent_id . ",
			position = " . (int) $clone_position . ",
            column_width = 0,
            body_backgroundimage = 0,
            set_update = 1,
            favorite = 0
			";

            $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));

            
            // speichert die Allgemeine Daten der Webseite
            $GLOBALS['mysqli']->query("INSERT INTO smart_langSite SET
			fk_id = '$site_id',
			lang = '$page_lang',
			title = '$site_title',
			site_url = '$site_url',
			menu_text = '$menu_text',
            menu_url = '$menu_url',
			meta_title = '$meta_title',
			meta_text = '$meta_text',
			meta_keywords = '$meta_keywords',
            menu_parent = 0,
            menu_position = 0,
            fb_title = '$fb_title',
            fb_text = '$fb_text',
            fb_image = '$fb_image'
			") or die(mysqli_error($GLOBALS['mysqli']));

            // Klonen von Felder und Zuordnung auf die neue Seite
            if ($_POST['clone_id']) {
                clone_site($_POST['clone_id'], $site_id);
            }
        } else {

            // UPDATE
            // Speichert Verknuepfung zur Page und Profil
            $sql = "UPDATE smart_id_site2id_page SET
			site_dynamic_id = " . (int) $site_dynamic_id . ",
			layout_id = " . (int) $profil_id . ",
			menu_disable = " . (int) $menu_disable . ",
            menu_newpage = " . (int) $menu_newpage . ",
			menubar_disable = " . (int) $menubar_disable . ",
			breadcrumb_disable =" . (int) $breadcrumb_disable . ",
			split_representation = '$split_representation',
			dynamic_site = " . (int) $dynamic_site . ",
			dynamic_name = '$dynamic_name',
			layout_array = '$array_new'
			WHERE site_id = '{$_POST['update_id']}' ";
            $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));

            // speichert die Allgemeine Daten der Webseite
            $GLOBALS['mysqli']->query("UPDATE smart_langSite SET
			title = '$site_title',
			site_url = '$site_url',
			menu_text = '$menu_text',
            menu_url = '$menu_url',
			meta_title = '$meta_title',
			meta_text = '$meta_text',
			meta_keywords = '$meta_keywords',
			fb_title = '$fb_title',
			fb_text = '$fb_text',
			fb_image = '$fb_image'
			WHERE fk_id = '{$_POST['update_id']}' AND lang = '$page_lang'
			") or die(mysqli_error($GLOBALS['mysqli']));

            set_update_site($_POST['update_id']);
        }

        save_smart_option($new_array, $_SESSION['smart_page_id'], $site_id);

        // Wenn noch keine Seite erzeugt worden ist, die diese in die smart_id_site2id_page eingetragen und positioniert
        if (! $_POST['update_id']) {

            if (! $_POST['clone_id']) {
                // Wenn noch keine Seite erzeugt worden ist, die diese in die smart_id_site2id_page eingetragen und positioniert
                if ($menu_position == 'end') {
                    $query_max_position = $GLOBALS['mysqli']->query("SELECT MAX(position) from smart_id_site2id_page WHERE page_id = '$page_id' ") or die(mysqli_error($GLOBALS['mysqli']));
                    $array_max_position = mysqli_fetch_array($query_max_position);
                    $new_position = $array_max_position[0] + 1;
                    $parent_id = 0;
                } else { // Postion und level auslesen

                    $query = $GLOBALS['mysqli']->query("SELECT parent_id, position FROM smart_id_site2id_page WHERE site_id = '$menu_position' ") or die(mysqli_error($GLOBALS['mysqli']));
                    $array = mysqli_fetch_array($query);
                    $new_position = $array['position'];
                    $parent_id = $array['parent_id'];
                    if (! $parend_id)
                        $parent_id = 0;
                    if ($page_id and $menu_position) {
                        // Platz schaffen für Seite in Menue
                        $GLOBALS['mysqli']->query("UPDATE smart_id_site2id_page SET position = position+1 WHERE site_id = '$menu_position' AND position > '$new_position' AND page_id = '$page_id'") or die(mysqli_error($GLOBALS['mysqli']));
                    }
                }
                // Postion setzen
                $GLOBALS['mysqli']->query("UPDATE smart_id_site2id_page SET 
                    parent_id = " . (int) $parent_id.", 
                    position = " . (int) $new_position."
                        WHERE site_id =  '$site_id' AND page_id = '$page_id' 
                ") or die(mysqli_error($GLOBALS['mysqli']));
                
            
            }

            
            set_update_site($site_id);
            
            if (! $_POST['clone_id']) {
                include ('../function_generate_element_template.php');
                // Hier werden die Elemente für die Seite erzeugt, je nach Auswahl der Vorlage
                include ('../ajax/generator_template_elements.php');
                
                generate_element_template($site_id, $generate_array);
                
                
            }
            echo (int) $site_id;
        } elseif ($list_id == 'site_list') {
            echo "update_list";
        } else {
            // echo ( int ) $site_id;
            echo "update";
        }
}

?>