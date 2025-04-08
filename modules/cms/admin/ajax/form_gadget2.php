<?php

//WIRD NICHT MEHR VERWENDET WEIL ES AUTO-SAVE GIBT
exit;



include_once ('../../../login/config_main.inc.php');
include_once ('../../config.inc.php');
// CALL Funtction for Save values in a TXT
include_once ('../../library/functions.php');

$gadget = $_POST['gadget'];
$layer_id = $_POST['update_id'];
$dynamic_modus = $GLOBALS['mysqli']->real_escape_string ( $_POST['dynamic_modus'] );
$dynamic_name = $GLOBALS['mysqli']->real_escape_string ( $_POST['dynamic_name'] );
$format = $GLOBALS['mysqli']->real_escape_string ( $_POST['format'] );
$layer_fixed = $GLOBALS['mysqli']->real_escape_string ( $_POST['layer_fixed'] );
$from_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['from_id'] );
$GLOBALS[set_ajax] = true;

/*
 * Guestbook addone - Eintragen in die Guestbook "smart_gadget_guestbook"
 */
if ($gadget == 'button') {
	$GLOBALS['mysqli']->query ( "DELETE FROM smart_gadget_button WHERE layer_id = '$layer_id' " );
	for($i = 1; $i <= 4; $i ++) {
		
		$button_text = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_text$i"] );
		$button_icon = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_icon$i"] );
		$button_color = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_color$i"] );
		$button_url = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_url$i"] );
		$button_link = $GLOBALS['mysqli']->real_escape_string ( $_POST["button_link$i"] );
		
		if ($button_text or $button_icon) {
			$GLOBALS['mysqli']->query ( "INSERT INTO smart_gadget_button SET 
				layer_id = '$layer_id',
				sequence = '$i',
				title = '$button_text',
				icon = '$button_icon',
				color = '$button_color',
				url = '$button_url',
				link = '$button_link' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		}
	}
} else if ($gadget == 'guestbook') {
	if ($_POST['guestbook_id'] == 'new')
		$_POST['guestbook_id'] = ''; // es efolgt ein neuer Eintrag
	
	$GLOBALS['mysqli']->query ( "REPLACE INTO smart_gadget_guestbook SET
	guestbook_id = '{$_POST['guestbook_id']}' ,
	page_id      = '{$_SESSION['smart_page_id']}',
	title        = '{$_POST['guestbook_name']}'
	" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$_POST[guestbook_id] = mysqli_insert_id ( $GLOBALS['mysqli'] );
} elseif ($gadget == 'youtube' or $gadget == 'embed') {
	if ($_POST['code'] and $_POST['select'] == 'youtube') {
		// Wenn nur Code angegeben wird, dann braucht dieser Url nicht umgewandelt werden
		if (strlen ( $_POST['code'] ) > 12)
			$_POST['code'] = parse_youtube ( $_POST['code'] );
	} elseif ($_POST['code'] and $_POST['select'] == 'vimeo') {
		// Wenn nur Code eingegeben wird, sonst umwandeln
		if (! is_numeric ( $_POST['code'] ))
			$_POST['code'] = parse_vimeo ( $_POST['code'] );
	}
}

$icon = strtolower ( $icon );

/*
 * change_photo - es wird nur der explorerpfad übergeben - die anderen Paramete müssen in dem Fall geladen werden und nachreicht werden
 * da sonst bestehen Einstellungen gelöscht werden würden
 */
if ($_POST['change_photo']) {
	if ($_POST['update_id']) {
		$sql = $GLOBALS['mysqli']->query ( "SELECT format,gadget_array,dynamic_modus,dynamic_name from smart_layer WHERE layer_id = '{$_POST['update_id']}'" );
		$array = mysqli_fetch_array ( $sql );
		$gadget_array = $array['gadget_array'];
		$dynamic_modus = $array['dynamic_modus'];
		$dynamic_name = $array['dynamic_name'];
		$format = $array['format'];
		$gadget_array_n = explode ( "|", $gadget_array );
		if ($array['gadget_array']) {
			foreach ( $gadget_array_n as $array ) {
				$array2 = preg_split ( "[=]", $array, 2 );
				// alle Paramete ausser explorer auslesen
				if ($array2[0] != 'explorer') {
					$_POST[$array2[0]] = $array2[1];
				}
			}
		}
	}
}

// Prüfen ob Colums leer sind, wenn nicht wird eine Wahrnung ausgeschrieben
// Diese Überprüfung ist noch nicht eingebunden

if ($icon)
	$icon = strtolower ( $icon );
if ($label_icon)
	$label_icon = strtolower ( $label_icon );

$gadget_array = array ();
// for($i = 1; $i <= 4; $i ++) {
// $gadget_array = array_merge ( $gadget_array, array ( "button_text$i" , "button_icon$i" , "button_color$i" , "button_url$i" , "button_link$i" ) );
// }

// Wird nur uebergeben damit die Formular den richtigen Reiter auf macht, danach wieder entfernen
$new_array = array_merge ( array ( learning_group_id ,
		learning_theme_id ,
		hallo_color ,
		variations ,
		cell_design ,
		cell_variation ,
		setting ,
		hide_in_smartphone ,
		no_fluid ,
		set_modal ,
		celled_off ,
		relaxed_off ,
		stretched ,
		column_relation ,
		button_size ,
		alt_icon ,
		alt_text ,
		alt_link ,
		alt_icon ,
		show_label ,
		label_text ,
		label_span ,
		label_color ,
		label_class ,
		labelIcon ,
		label_link ,
		label_size ,
		camp_key ,
		camp_key_alt ,
		button_inline ,
		button_fluid ,
		secondname_right ,
		link_reg_success ,
		button_color ,
		show_intro ,
		button_text ,
		recaptcha ,
		select ,
		script ,
		select_plugin ,
		select_dynamic ,
		text ,
		marquee ,
		autoplay ,
		rel ,
		showinfo ,
		title ,
		file ,
		date ,
		css ,
		icon ,
		button ,
		color ,
		newsletter_group ,
		facebook ,
		fb_link ,
		substructure ,
		show_all ,
		style ,
		margin_lr ,
		align ,
		info_position ,
		explorer ,
		send_button ,
		link ,
		set_target ,
		url ,
		diameter ,
		placeholder ,
		width ,
		height ,
		vwidth ,
		vheight ,
		size ,
		sort ,
		direction ,
		dimension ,
		segment ,
		segment_color ,
		segment_inverted ,
		segment_disabled ,
		segment_grade ,
		segment_or_message ,
		segment_size ,
		segment_width ,
		segment_type ,
		segment_compact ,
		guestbook_id ,
		time ,
		successful_message ,
		show ,
		folder ,
		representation ,
		thumb_width ,
		thumb_height ,
		skin ,
		size2 ,
		col ,
		show_thumbnail ,
		show_title ,
		smoothHeight ,
		slide_view ,
		image_resize ,
		slideshow ,
		slideshowSpeed ,
		animationLoop ,
		animation ,
		code ,
		placeholder ,
		icon ,
		start_time ,
		aspect_ratio ,
		resize ,
		destination ,
		show_zip ,
		show_clients ,
		show_sorts ,
		show_firstname ,
		show_secondname ,
		show_title ,
		after_click ,
		receive_email ,
		style_div ,
		button_url ,
		button_link ,
		gallery_style,
		subject_text,
		menu_version
), $gadget_array );

foreach ( $new_array as $key ) {
	$value = $GLOBALS['mysqli']->real_escape_string ( $_POST[$key] );
	if ($value) {
		$array_new .= $key . "=" . $value . "|";
		$array_new = preg_replace ( "/|/", "", $array_new );
	}
}

if ($_POST['select'] == 'placeholder') {
	$gadget = 'placeholder';
}

// check war vorher der fixed-Layer eingeschalten
$check_layer_fixed_query = $GLOBALS['mysqli']->query ( "SELECT layer_fixed FROM smart_layer WHERE layer_id='$layer_id' " );
$check_layer_fixed_array = mysqli_fetch_array ( $check_layer_fixed_query );
$set_layer_fixed = $check_layer_fixed_array['layer_fixed'];
if ($set_layer_fixed == 0)
	$set_layer_fixed = '';

$GLOBALS['mysqli']->query ( "UPDATE smart_layer SET
gadget_array = '$array_new',
gadget       = '$gadget',
dynamic_modus = '$dynamic_modus',
dynamic_name = '$dynamic_name',
format = '$format',
layer_fixed = '$layer_fixed',
from_id = '$from_id'
WHERE layer_id='$layer_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

// Wenn layer fixiert wird oder war, wird gesamte Seite neu geladen
if ($layer_fixed != $set_layer_fixed) {
	echo 'reload';
	return;
}

// Textfeld muss nicht neu geladen werden
// if ($gadget == 'textfield') { echo 'close'; return; }

$_POST['set_textfield'] = '1';
if ($layer_id)
	$set_update = '1'; // Dieser Parameter wird übergeben damit CKeditor für das bearbeitete Feld zur Verfügung steht

set_update_site ();
// generate Textfield for the gallery
include ('layer_new_inc.php');