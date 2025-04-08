<?php
include_once ('../../../login/config_main.inc.php');

include_once ('../../library/functions.php');

$page_id = $_SESSION['smart_page_id'];
$page_lang = $_SESSION['page_lang'];
$module_id = $_POST['module_id'];
if ($_POST['layer_id'])
    $layer_id = $_POST['layer_id'];

if ($module_id) {
    $_POST['set_textfield'] = true;
}

/*
 * Aufruf bei Update von Gadgets
 */
if ($layer_id) {
    // Wert wird übergeben und durch bestehenden Content ersetzt (nur Inhalt ohne layer)
    $GLOBALS['set_ajax'] = true;
    echo show_element($layer_id);
    return;
}

if (! $layer_content) {
    $layer_content = '<br>Zum Bearbeiten einfach mit der Maus in das Feld klicken und Text direkt bearbeiten.<br><br>';
}

$gadget = preg_replace("[button_]", "", $module_id);
if ($gadget == 'new_textfield')
    $gadget = ''; // Wenn es nur ein Textfeld ist wird der Parameter nicht übergeben

// Layer (verschiebar) UPDATE
if ($_POST['layer_id'] != 'button_layershort' and $_POST['layer_id'] != '') {
    $layer_id = $_POST['layer_id'];
    $layer_update = TRUE;
    // TODO: Optionen Updaten
    // echo "test";
    $GLOBALS['mysqli']->query("UPDATE `{$_SESSION['db_smartkit']}`.`smart_layer` SET
	`matchcode` = '{$_POST['layer_matchcode']}',
	`layer_fixed` = '{$_POST['layer_fixed']}'
	WHERE `smart_layer`.`layer_id` = $layer_id LIMIT 1 ;") or die(mysqli_error($GLOBALS['mysqli']));

    // Löscht Datenbankfelder
    $GLOBALS['mysqli']->query("DELETE FROM smart_id_layer2id_site WHERE layer_id = '$layer_id' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));
    $GLOBALS['mysqli']->query("DELETE FROM smart_id_layer2id_page WHERE layer_id = '$layer_id' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));
} // Textfeld
else {

    $layer_x  = 0;
    
    $layer_y  = 0;
    
    $layer_h = 0;
    
    $layer_w = 0;
    
    
    if ($_POST['set_textfield']) {
        $site_id = $_SESSION['site_id'];
        $GLOBALS['mysqli']->query("UPDATE smart_layer SET sort = sort+1 where site_id = '$site_id' ") or die(mysqli_error($GLOBALS['mysqli']));
        $position = 'left';
    } else {
        // Defaultwerte für das erste erzeugen eines Layers
        if (! $_SESSION['layer_x'])
            $_SESSION['layer_x'] = '50';
        else
            $_SESSION['layer_x'] = $_SESSION['layer_x'] + '20';
        if (! $_SESSION['layer_y'])
            $_SESSION['layer_y'] = '50';
        else
            $_SESSION['layer_y'] = $_SESSION['layer_y'] + '20';
        $layer_h = '100';
        $layer_w = '250';

        $layer_x = $_SESSION['layer_x'];
        $layer_y = $_SESSION['layer_y'];
    }

    if (!$field) $field = 0;
    
    // Anlegen von einem Layer
    $GLOBALS['mysqli']->query("INSERT INTO smart_layer SET
	matchcode     = '{$_POST['layer_matchcode']}',
	page_id       = '$page_id',
	field         = '$field',
	site_id       = '$site_id',
	layer_x       = '$layer_x',
	layer_y       = '$layer_y',
	layer_h       = '$layer_h',
	layer_w       = '$layer_w',
	layer_fixed   = '0',
	position      = '$position',
	gadget        = '$gadget',
    gadget_id     = 0,
    sort = 0,
    dynamic_name = '',    
    dynamic_modus  = 0,
    format= 0, 
    hidden = 0,    
    archive = 0,
    from_id = 0,
    splitter_layer_id = 0,
	gadget_array  = '$array_new',
    set_textfield = 0
	") or die(mysqli_error($GLOBALS['mysqli']));

    // gadget_id = '$gadget_id',

    // Auslesen der Menu_ID
    $layer_id = mysqli_insert_id($GLOBALS['mysqli']);

    // SprachLayer einspielen
    $GLOBALS['mysqli']->query("INSERT INTO smart_langLayer SET
	fk_id = '$layer_id',
	lang  = '{$_SESSION['page_lang']}',
	text  = '$layer_content' ") or die(mysqli_error($GLOBALS['mysqli']));
}
// Single Save
if ($_POST['layer_allocation']) {
    $GLOBALS['mysqli']->query("INSERT INTO smart_id_layer2id_site VALUES ('$layer_id','{$_SESSION['site_id']}')") or die(mysqli_error($GLOBALS['mysqli']));
} // Hole Page
else {

    $GLOBALS['mysqli']->query("INSERT INTO smart_id_layer2id_page VALUES ('$layer_id','$page_id')") or die(mysqli_error($GLOBALS['mysqli']));
}

// anlegen eines verschiebbaren Textfeldes
if ($_POST['set_textfield']) {

    // echo show_textfield ( $layer_id, $gadget, 'new' );
    echo show_element($layer_id, 'new');
} else {
    if ($layer_update)
        echo "update";
    else
        echo show_layer($layer_id, $layer_content, $layer_x, $layer_y, $layer_h, $layer_w, 'new');
}
set_update_site();
?>