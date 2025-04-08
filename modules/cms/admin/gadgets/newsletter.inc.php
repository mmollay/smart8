<?php
include_once (__DIR__ . '/functions.php');

if (!$update_id) {
	$show_firstname = '1';
	$show_secondname = '1';
	$icon = 'mail';
	// $button_text = "$('#button_text').val('Weiter');";
	$button_text = "Anmelden";
}

if (!$format)
	$format = "[[newsletter]]";

if ($gadget == 'formulardesign_list') {
	$info = 'Für ein Design braucht es auch ein Listbuilding. Um diese zu bearbeiten oder zu erzeugen klicke auf Listbuilding';
} else
	$info = 'Jede im Formular eingegragene Adresse wird in diese Liste eigetragen.';

$onLoad .= "
		represention($('.setting:checked').attr('id'));
		$('.setting').bind('keyup change',function() { represention(this.id) });
				
		function represention(id){
			$('#row_camp_key,#row_button_inline,.buttons_url').hide();
			if (id =='sign_in' ){ $('#row_camp_key,#row_button_inline').show();  }
			else if (id =='to_complete' ) { $('.buttons_url').show(); }
		}
		";

if ($_POST['list_id'] != 'formulardesign_list') {
	$array_sites = GenerateArraySql("SELECT * FROM smart_langSite INNER JOIN smart_id_site2id_page ON smart_langSite.fk_id = smart_id_site2id_page.site_id and lang='{$_SESSION['page_lang']}' AND page_id='{$_SESSION['smart_page_id']}'", 'title'); // %var% die ausgegeben werden sol
}

$array_setting = array("sign_in" => "Anmelden", "to_complete" => "Vervollständigen");
if (!$setting)
	$setting = 'sign_in';

/* ACCORD1 */
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'ui styled accordion', 'text' => "<div class='active title'><i class='icon dropdown'></i>Allgemein</div>");
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'active content');

$arr['field']['setting'] = array('tab' => 'first', 'label' => 'Anzeigeart', 'type' => 'radio', 'grouped' => true, 'validate' => true, 'array' => $array_setting, 'emptyfield' => '--Liste w&auml;hlen--', 'value' => $setting);

$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'buttons_url ui message'); // fields two

// Wird bei Einbinudung nicht angezeigt
if ($_POST['list_id'] != 'formulardesign_list') {
	$arr['field']["button_url"] = array('tab' => 'first', 'label' => 'Seitenverlinkung', 'type' => 'dropdown', 'array' => $array_sites, 'value' => $button_url);
}

$arr['field']["button_link"] = array('tab' => 'first', 'label' => 'URL', 'label_left' => 'oder', 'type' => 'input', 'value' => $button_link, 'placeholder' => 'http://');
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields' );
$arr['field']['camp_key'] = array(
	'tab' => 'first',
	'label' => "Listbuilding <a href='../ssi_newsletter/' target=new>[Einstellungen]</a> ",
	'info' => 'Einstellungen für das Listbuilding werden im Newsletter-System vorgenommen',
	'type' => 'dropdown',
	'array' => call_array_formular($cfg_mysql['db_nl']),
	'emptyfield' => '--Liste w&auml;hlen--',
	'value' => $camp_key
);

// $arr['field']['button_setting'] = array ( 'tab' => 'first' , 'type' => 'button' , 'value' => 'Einstellungen' , 'class_button' => 'fluid mini' , 'tooltip' => 'Hier klicken um Listbuidingeinstellungen zu bearbeiten' , 'onclick' => "window.open('../ssi_newsletter/')" );

$arr['field']['set_focus'] = array('tab' => 'first', 'label' => 'Focus setzen', 'type' => 'checkbox', 'value' => $set_focus, 'info' => 'Cursor springt automatisch in das Eingeabefeld hinein');

/* ACCORD1_CLOSE */
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

/* ACCORD2 */
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'text' => "<div class='title'><i class='icon dropdown'></i>Zusatzfelder</div>");
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'content');

$arr['field']['show_intro'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Anrede', 'value' => $show_intro);
$arr['field']['show_firstname'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Vorname', 'value' => $show_firstname);
$arr['field']['show_secondname'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Nachname', 'value' => $show_secondname);
$arr['field']['show_zip'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Plz', 'value' => $show_zip);

$arr['field']['secondname_right'] = array('tab' => 'first', 'type' => 'checkbox', 'label' => 'Nach-neben Vornamen', 'value' => $secondname_right);
$arr['field']['show_title'] = array('tab' => 'first', 'type' => 'checkbox', 'label' => 'Labels anzeigen', 'value' => $show_title);

/* ACCORD1_CLOSE */
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'text' => "<div class='title'><i class='icon dropdown'></i>Sende-button</div>");
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'content');

$arr['field']['button_inline'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Aktionbutton Inline anzeigen', 'value' => $button_inline);
$arr['field']['button_fluid'] = array('tab' => 'first', 'type' => 'toggle', 'label' => 'Button strecken', 'value' => $button_fluid);
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields three' );

$arr['field']['button_text'] = array('tab' => 'first', "label" => "Button-Text", "type" => "input", 'value' => $button_text);
$arr['field']['button_color'] = array('tab' => 'first', 'label' => "Farbe", 'type' => 'dropdown', 'array' => 'color', 'value' => $button_color);
$arr['field']['button_size'] = array('tab' => 'first', 'label' => 'Größe', 'type' => 'dropdown', "array" => $array_size, 'value' => $button_size);
$arr['field']['icon'] = array('tab' => 'first', 'label' => "Icon", 'type' => 'icon', 'value' => $icon);

$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');

$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'text' => "<div class='title'><i class='icon dropdown'></i>Texte <span class='tooltip-left' data-html='Platzhalter für das Formular: [[newsletter]]'><i class='icon help circular'></i></span></div>");
$arr['field'][] = array('tab' => 'first', 'type' => 'div', 'class' => 'content');

$arr['field']['format'] = array('tab' => 'first', 'type' => 'ckeditor_inline', 'toolbar' => 'mini', 'value' => $format);
//$arr['field']['infotext'] = array ( 'tab' => 'first' , 'type' => 'text' , 'label' => 'Platzhalter: <b>[[newsletter]]</b>' );
$arr['field'][''] = array('tab' => 'first', 'type' => 'button', 'class_button' => 'mini blue', 'value' => 'Text übernehmen', 'onclick' => "save_value_element('$update_id','format',$('#format').html(),'format');");
/* ACCORD_CLOSE */

$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');
$arr['field'][] = array('tab' => 'first', 'type' => 'div_close');

/*
 * $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
 * $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'header' , 'text' => 'Alternativer Button' , 'class' => 'small' );
 * // $arr['field']['camp_key_alt'] = array ( 'tab' => 'first' , 'label' => 'Formular' , 'type' => 'dropdown' , 'class' => 'search' , 'array' => call_array_formular () , 'emptyfield' => '--Kampagne w&auml;hlen--' , 'value' => $camp_key_alt , "info" => 'Alternativer Absendebutton' );
 * $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields three' );
 * $arr['field']['alt_icon'] = array ( 'tab' => 'first' , 'label' => "Icon" , 'type' => 'input' , 'value' => $alt_icon ,  'label_right' => "$list_icons_alt" );
 * $arr['field']['alt_text'] = array ( 'tab' => 'first' , "label" => "Text" , "type" => "input" , 'value' => $alt_text );
 * $arr['field']['alt_link'] = array ( 'tab' => 'first' , "label" => "Link" , "type" => "input" , 'value' => $alt_link );
 * $arr['field']['alt_color'] = array ( 'tab' => 'first' , 'label' => "Farbe" , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $alt_color );
 * $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
 * $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
 */

// Wenn die Formular-Daten für die Newsletterseite aufgerufen werden spingt System nach Abruf der Arrays gleich wieder zurück
// Abarbeitung findet auf ssi_newsletter/ajax/form_edit.php statt
if ($gadget == 'formulardesign_list') {
	return;
	exit();
}
