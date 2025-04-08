<?php

/**
 * *************************************************************
 * FUNCTION PAGE GENERATOR
 * Seite erzeugen
 * *************************************************************
 */
// $array['site_title'] = 'Funnel';
// $array['site_url'] = 'funnel';
// $array['page_id'] = $page_id;
// $funnel_site_id = generate_site4funnel ( $array );
function generate_site4funnel($array)
{
    // Speichert Seite (Wird benötigt fuer das erzeugen der Vorlage)!!!!
    $GLOBALS['mysqli']->query("INSERT INTO smart_site SET matchcode = '$matchcode' ") or die(mysqli_error($GLOBALS['mysqli']));
    $site_id = mysqli_insert_id($GLOBALS['mysqli']);
    // Speichert Verknuepfung zur Page und Profil
    $sql = "INSERT INTO smart_id_site2id_page SET
			site_id   = '$site_id',
            uuid  = UUID(),
			page_id   = '{$array['page_id']}',
			parent_id = '{$array['parent_id']}',
			menu_disable = '1',
			menubar_disable = '1',
			breadcrumb_disable = '1',
			funnel_short = '{$array['funnel_short']}',
			funnel_id = '{$array['funnel_id']}'  
			";
    $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));

    // speichert die Allgemeine Daten der Webseite
    $GLOBALS['mysqli']->query("INSERT INTO smart_langSite SET
			fk_id = '$site_id',
			lang = '{$_SESSION['page_lang']}',
			title = '{$array['site_title']}',
			site_url ='{$array['site_url']}'
			") or die(mysqli_error($GLOBALS['mysqli']));
    return $site_id;
}

/**
 * ******************************************************************
 * Erzeugt für die jeweilige Seite ein Element oder mehrere Elemente
 * ******************************************************************
 */
// $generate_array['splitter1'] = array ( 'position' => 'left' , 'gadget' => 'splitter' , 'gadget_array' => "column_relation=1|cell_design=empty|parallax_image=$src_head|parallax_mode=1|parallax_show=1|parallax_filter=1" , layer_id => $layer_id );
// $generate_array['title'] = array ('splitter_layer_id' => 'splitter1', 'position' => 'left' , 'gadget' => 'textfield' );
// $generate_array['text'] = array ('splitter_layer_id' => 'splitter1', 'position' => 'left' , 'gadget' => 'button' , 'gadget_array' => 'no_fluid=1|button_size=large|' , , 'array_button' => array ( 'title' => 'RABATTCODE JETZT SICHERN' , 'color' => 'orange' , 'icon' => 'star' ) );
// $generate_array['gallery'] = array ('splitter_layer_id' => 'splitter1', 'position' => 'right' , 'gadget' => 'gallery' , 'gadget_array' => "folder=/amazon/$amazon_id|after_click=resize|" );
function generate_element_template($site_id, $generate_array)
{

    // Erzeugen der Felder nach Reihenfolge siehe generate_array
    foreach ($generate_array as $id => $array) {

        // html säubern von Leerzeichen
        // $GLOBALS[$input] = preg_replace("//","",$GLOBALS[$input]);

        if ($array['gadget'] == 'splitter') {
            $GLOBALS['mysqli']->query("REPLACE INTO smart_layer SET
			layer_id =      " . (int) $array['layer_id'] . ",
			site_id       = '$site_id',
            page_id   = " . (int) $_SESSION['smart_page_id'] . ",
            matchcode = '',
            layer_x = 0,
            layer_y = 0,
            layer_h = 0,
            layer_w = 0,
            field = 0,
            set_textfield = 0,
            gadget_id  =0,
            layer_fixed = 0,
            gadget_array = '',
            dynamic_modus = 0,
            dynamic_name = '',
            format = '',
            archive = 0,
            hidden = 0,
            from_id = 0,
			sort          = " . (int) $array['sort'] . ",
			splitter_layer_id = " . (int) $GLOBALS[$array['splitter_layer_id']] . ",
			position      = '{$array['position']}',
			gadget        = '{$array['gadget']}'
			") or die(mysqli_error($GLOBALS['mysqli']));
            $GLOBALS[$id] = mysqli_insert_id($GLOBALS['mysqli']);

            // einspielen der Option-Elemente
            foreach ($array['gadget_array'] as $element_name => $element_value) {
                $GLOBALS['mysqli']->query("INSERT INTO smart_element_options SET
				element_id    = '{$GLOBALS[$id]}',
				option_name = '$element_name',
				option_value  = '$element_value'
				") or die(mysqli_error($GLOBALS['mysqli']));
            }
        } else {

            if ($array['gadget'] == 'textfield') {
                if ($array['text']) {
                    // Wenn Text direkt über array übergeben wirdd
                    $text = $array['text'];
                } else {
                    // Wenn wert global übergeben wird
                    global ${$id};
                    $text = ${$id};
                }
                $text = str_replace(array("\n","\r","\t"), '', $text);
                $text = preg_replace("/\s+/", " ", trim($text));
                $text = $GLOBALS['mysqli']->real_escape_string($text);
            }

            // $splitter_layer_id = $GLOBALS[$array['splitter_layer_id']];
            // ${$array2['splitter_layer_id']};

            // Neues Feld in der Seite anlegen aber nicht beim Clonen
            $GLOBALS['mysqli']->query("INSERT INTO smart_layer SET
				site_id       = '$site_id',
                page_id   = " . (int) $_SESSION['smart_page_id'] . ",
                matchcode = '',
                layer_x = 0,
                layer_y = 0,
                layer_h = 0,
                layer_w = 0,
                field = 0,
                set_textfield = 0,
                gadget_id  =0,
                layer_fixed = 0,
                gadget_array = '',
                dynamic_modus = 0,
                dynamic_name = '',
                format = '',
                archive = 0,
                hidden = 0,
                from_id = 0,
                sort          = 0,
				position      = '{$array['position']}',
				gadget        = '{$array['gadget']}',
				splitter_layer_id = '{$GLOBALS[$array['splitter_layer_id']]}'
				") or die(mysqli_error($GLOBALS['mysqli']));
            $set_layer_id = mysqli_insert_id($GLOBALS['mysqli']);

            // Button anlegen
            if ($array['gadget'] == 'button') {
                $GLOBALS['mysqli']->query("INSERT INTO smart_gadget_button SET
				layer_id       = '$set_layer_id',
				sequence       = 1,
				title          = '{$array['title']}',
				icon           = '{$array['icon']}',
				color  		   = '{$array['color']}'
				") or die(mysqli_error($GLOBALS['mysqli']));
            }

            // Anlegen wenn es sich um ein Textfeld handelt und ein Content vorhanden
            // if ($array['gadget'] == 'textfield' AND $GLOBALS[$input] )
            // SprachLayer einspielen
            $GLOBALS['mysqli']->query("INSERT INTO smart_langLayer SET
			fk_id = '$set_layer_id',
			lang  = '{$_SESSION['page_lang']}',
			text  = '$text' ") or die(mysqli_error($GLOBALS['mysqli']));

            if (is_array($array['gadget_array'])) {
                // einspielen der Option-Elemente
                foreach ($array['gadget_array'] as $element_name => $element_value) {
                    $GLOBALS['mysqli']->query("INSERT INTO smart_element_options SET
				element_id    = $set_layer_id,
				option_name = '$element_name',
				option_value  = '$element_value'
				") or die(mysqli_error($GLOBALS['mysqli']));
                }
            }
        }
    }
}
