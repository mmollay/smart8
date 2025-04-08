<?php
include ('../../../../login/config_main.inc.php');

$site_id = $_SESSION ['site_id'];

include_once ("../../../smart_form/include_form.php");
$id = $_POST ['id'];
$id = preg_replace ( '[row_field-]', '', $id );

// Prüfen ob der User die Berechtigung hat auf dieser Seite was zu ändern
$query = $GLOBALS ['mysqli']->query ( "SELECT b.type type, label, default_value, value, set_email, newsletter_field, validate, b.text text, placeholder, help, setting_array FROM smart_layer a LEFT JOIN smart_formular b ON a.layer_id = b.layer_id WHERE field_id = '$id' and site_id = $site_id " );
$array = mysqli_fetch_array ( $query );
$validate = $array ['validate'];
$label = $array ['label'];
$ckeditor_text = $array ['text'];
$placeholder = $array ['placeholder'];
$default_value = $array ['default_value'];
$set_email = $array ['set_email'];
$newsletter_field = $array ['newsletter_field'];
$help = $array ['help'];
$text = $array ['text'];

if ($array ['setting_array']) {
	$gadget_array_n = explode ( "|", $array ['setting_array'] );
	foreach ( $gadget_array_n as $set_array ) {
		$array2 = preg_split ( "[=]", $set_array, 2 );
		$GLOBALS [$array2 [0]] = $array2 [1];
	}
}

$array_value = json_decode ( $array ['value'], true );
if (is_array ( $array_value )) {
	foreach ( $array_value as $key => $value ) {

		$value_list .= "\n$key => $value";
	}
}

// Eingabefelder und Text
if ($array ['type'] == 'checkbox')
	$array_nl_field = array ('set_newsletter' => 'Checkbox Abonnieren' );
elseif ($array ['type'] == 'radio' or $array ['type'] == 'select')
	$array_nl_field = array ('intro' => 'Anrede m=>Mann, f=>Frau','country' => 'Land at=>Österreich, de=>Deutschland, ch=>Schweiz' );
else
	$array_nl_field = array ('email' => 'Email','company_1' => 'Firma','company_2' => 'Firma (Zusatz)','firstname' => 'Vorname','secondname' => 'Nachname','street' => 'Strasse','city' => 'Stadt','zip' => 'PLZ','web' => 'Internet','zip' => 'PLZ','telefon' => 'Telefon','birth' => 'Geburtstag',
			'commend' => 'Zusatzinfos','commend2' => 'Zusatzinfos 2','uploads' => 'Hochgeladene Dateien' );

$arr ['field'] [] = array ('tab' => 'form_first','type' => 'div','class' => 'two fields' );

$arr ['field'] ['label'] = array ('tab' => 'form_first','label' => 'Titel','type' => 'input','value' => $label );

if ($array ['type'] != 'textarea') {
	$arr ['field'] ['newsletter_field'] = array ('tab' => 'form_first','label' => 'Zuweisung für Newsletter-Kontakt','array' => $array_nl_field,'value' => $newsletter_field,'type' => 'dropdown','clear' => true );
}

$arr ['field'] [] = array ('tab' => 'form_first','type' => 'div_close' );

if ($array ['type'] == 'slider') {

	$arr ['field'] [] = array ('tab' => 'form_first','type' => 'div','class' => 'two fields' );
	$arr ['field'] ['min'] = array ('tab' => 'form_first','label' => 'Minimal','type' => 'input','value' => $min,'placeholder' => 0 );
	$arr ['field'] ['max'] = array ('tab' => 'form_first','label' => 'Maximal','type' => 'input','value' => $max,'placeholder' => 10 );
	$arr ['field'] ['unit'] = array ('tab' => 'form_first','label' => 'Einheit','type' => 'input','value' => $unit,'placeholder' => 'Tage' );
	$arr ['field'] ['class_color'] = array ('tab' => 'form_first','label' => 'Farbe','type' => 'dropdown','value' => $class_color,'array' => 'color' );
	$arr ['field'] ['class_ticked'] = array ('tab' => 'form_first','label' => 'Mit Rasterung','type' => 'checkbox','value' => $class_ticked );

	$arr ['field'] [] = array ('tab' => 'form_first','type' => 'div_close' );
}

if ($array ['type'] == 'select' or $array ['type'] == 'radio') {
	$arr ['field'] ['value'] = array ('search' => true, 'tab' => 'form_first','label' => 'Wert(e)','type' => 'textarea','value' => $value_list );
}

if ($array ['type'] == 'checkbox') {
	$arr ['field'] ['default_value'] = array ('tab' => 'form_first','label' => 'Default mäßig aktivieren','type' => 'checkbox','value' => $default_value );
}

if ($array ['type'] != 'text') {
	$arr ['field'] ['validate'] = array ('tab' => 'form_first','label' => 'Zwingend ausfüllen','type' => 'checkbox','value' => $validate );
	$arr ['field'] ['help'] = array ('tab' => 'form_first','label' => 'Info-Feld','type' => 'input','value' => $help,'info' => 'Erklärungstext für das jeweilige Eingabefeld' );
}

if ($array ['type'] == 'textarea') {
	$arr ['field'] ['rows'] = array ('tab' => 'form_first','label' => 'Höhe','type' => 'dropdown','placeholder' => '','array' => array (2,3,4,5,6,7,8,10,11,12,13,14 ),'value' => $rows,'info' => 'Anzahl der Höhe der Felder' );
}

$arr ['field'] ['placeholder'] = array ('tab' => 'form_first','label' => 'Platzhalter','type' => 'input','value' => $placeholder,'info' => 'Eingegrauter Text, dient als Beispiel den Nutzer' );

if ($array ['type'] == 'text') {
    $arr ['field'] ['text'] = array ('type' => 'ckeditor','toolbar' => 'mini','value' => $text );
}

if ($array ['type'] == 'input' or $array ['type'] == 'radio' or $array ['type'] == 'select')
	$arr ['field'] ['default_value'] = array ('tab' => 'form_first','label' => 'Vorgegebener Wert','type' => 'input','value' => $default_value,'info' => 'Voreingetragener Inhalt der aber verändert werden kann.' );

$array_semgent_or_message2 = array ('segment' => 'Segment','message' => 'Infofeld' );
$array_segment_grade2 = array ('primary' => 'Primär','secondary' => 'Sekundär','tertiary' => 'Tertiär' );

if (! $segment_or_message) {
	$segment_or_message = 'segment';
}

// $arr['tab'] = array ( 'class' => "pointing secondary" , 'content_class' => "secondary" , 'tabs' => [ "form_first" => "Allgmein" , "form_sec" => "Design" ] , 'active' =>'form_first' );

// $arr['field']['field_segment'] = array ( 'tab' => 'form_sec' , 'type' => 'checkbox' , 'label' => 'Rahmen anzeigen' , 'value' => $field_segment , 'info' => 'Inhalt wird in eine weiße Box mit Rahmen gepackt' );
// $arr['field'][] = array ( 'tab' => 'form_sec' , 'type' => 'div' , 'class' => 'inline fields' );
// $arr['field']['segment_or_message'] = array ( 'tab' => 'form_sec' , 'type' => 'dropdown' , 'array' => $array_semgent_or_message2 , 'value' => $segment_or_message , 'placeholder' => 'Darstellung' );
// $arr['field']['segment_color'] = array ( 'tab' => 'form_sec' , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $segment_color , 'placeholder' => 'Farben' );
// $arr['field']['segment_grade'] = array ( 'tab' => 'form_sec' , 'type' => 'dropdown' , 'array' => $array_segment_grade2 , 'value' => $segment_grade , 'placeholder' => 'Grad' );
// $arr['field'][] = array ( 'tab' => 'form_sec' , 'type' => 'div_close' );
// $arr['field'][] = array ( 'tab' => 'form_sec' , 'type' => 'div' , 'class' => 'inline fields' );
// $arr['field']['segment_inverted'] = array ( 'tab' => 'form_sec' , 'type' => 'checkbox' , 'label' => 'Farbe im Hintergrund anzeigen' , 'value' => $segment_inverted , 'info' => 'Farbe wird für den Hintergrund verwendet' );
// // $arr['field']['segment_disabled'] = array ( 'tab' => 'form_sec' , 'type' => 'checkbox' , 'label' => 'Disabled' , 'value' => $segment_disabled , 'info' => 'Gesamte Inhalt wird "entkräftet"' );
// $arr['field'][] = array ( 'tab' => 'form_sec' , 'type' => 'div_close' );

$arr ['hidden'] ['save_id'] = $id;
$arr ['form'] = array ('action' => 'gadgets/formular/admin/edit_field2.php','id' => "form_formular_field",'class' => 'small','inline' => 'true' );

$arr ['ajax'] = array ('success' => "if (data == 'ok') {  $('.ui.modal').modal('hide'); load_field($id); }",'dataType' => 'html' );

// $arr['buttons'] = array ( 'align' => 'center' );
$arr ['button'] ['submit'] = array ('value' => "Speichern",'color' => 'blue' );
$arr ['button'] ['close'] = array ('value' => "Abbrechen",'js' => "$('.ui.modal').modal('hide');" );

$output_form = call_form ( $arr );
echo $output_form ['js'];
echo $output_form ['html'];

