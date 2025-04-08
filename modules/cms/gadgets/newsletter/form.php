<?php
$layer_id = $_POST['layer_id'];

include (__DIR__ . "/../../gadgets/config.php");
include_once ('functions.php');
include_once ('../function.inc.php');
call_layer_parameter ( $layer_id );

if (! $setting)
	$setting = 'sign_in';

if ($setting == 'sign_in' && ! $camp_key) {
	$output = "<div class='message ui info'>Für den Versandt ist noch kein Liste gewählt!</div>";
	echo $output;
	return;
	// Wenn kein gültiger Client (verify_key vom Newsletter-User
} elseif ($setting == 'to_complete' && ! $client_token && ! $_SESSION['admin_modus']) {
	$output = "<div class='message ui info'>Dieser Inhalt wird nur mit einem gültigen User angezeigt.</div>";
	echo $output;
	return;
}

if ($set_focus)
	$focus = 'focus';

$check_promition_is_active = check_promotion_is_active ( $cfg_mysql['db_nl'], $camp_key );

// Check sind noch genug Codes vorhanden sofern eine Promotion an das Formular angebunden ist
if ($check_promition_is_active) {
	$output = $check_promition_is_active;
	echo $output;
	return;
}

// Setzt anderen Pfad bei Aufruf über IFRAME
if ($call_iframe) {
	$action = '../../ssi_smart/gadgets/newsletter/submit.php';
} else {
	include_once (__DIR__ . "/../../smart_form/include_form.php");
	$action = 'gadgets/newsletter/submit.php';
}

if (! $button_text)
	$button_text = 'Newsletter anmelden';
if (! $button_color)
	$button_color = 'green';

if (! $icon)
	$button_icon = "<i class='icon mail outline'></i>";
else
	$icon = "<i class='icon $icon'></i>";

if ($alt_icon)
	$alt_icon = "<i class='icon $alt_icon'></i>";

$arr['form'] = array ( 'id' => "form_newsletter$camp_key" , 'class' => "$segment_size" , 'inline' => false , 'action' => $action );

$arr['ajax'] = array ( 'datatype' => 'script' );

if ($show_title) {
	$label_intro = 'Anrede';
	$label_firstname = 'Vorname';
	$label_secondname = 'Nachname';
	$label_zip = 'PLZ';
	$label_email = 'Email';
} else {
	$placeholder_email = 'Email';
}
if ($button_fluid) $button_fluid = 'fluid';

if ($button_inline) {
	$label_right = "$icon$button_text";
} else {
	$arr['buttons'] = array ( 'align' => 'center' );
	$arr['button']['submit'] = array ( 'value' => $icon . $button_text ,  'color' => $button_color, 'class' => "$button_fluid $button_size" );
}

// Alternativer Button
if ($alt_text and $alt_link) {
	$arr['button']['submit2'] = array ( 'value' => $alt_icon . $alt_text ,  'color' => $alt_color , 'class' => "submit $button_size" , 'onclick' => "location.href='$alt_link'" );
}

if ($client_token) {
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM {$cfg_mysql['db_nl']}.contact WHERE verify_key = '$client_token' " ) or die ( $GLOBALS['mysqli']->query () );
	$client_array = mysqli_fetch_array ( $query );
}

if ($show_intro) {
	$arr['field']['intro'] = array ( 'value' => $client_array['sex'] , 'label' => $label_intro , 'type' => 'radio' ,  'validate' => 'Anrede auswählen' , 'placeholder' => '--Anrede wählen--' , 'array' => array ( 'm' => 'Herr' , 'f' => 'Frau' , 'c' => 'Firma' ) ,  'focus' => $focus , 'value' => 'm' );
}

if (($show_firstname and $show_secondname) and $secondname_right) {
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' ); // 'label'=>'test'
}

if ($show_firstname) {
	$arr['field']['firstname'] = array ( 'value' => $client_array['firstname'] , 'label' => $label_firstname , 'type' => 'input' , 'placeholder' => 'Vorname' ,  'validate' => 'Vornamen eingeben' ,  'focus' => $focus );
} else {
	$email_focus = $focus;
}

if ($show_secondname) {
	$arr['field']['secondname'] = array ( 'value' => $client_array['secondname'] , 'label' => $label_secondname , 'type' => 'input' , 'placeholder' => 'Nachname' ,  'validate' => true );
}

if (($show_firstname and $show_secondname) and $secondname_right) {
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
}

if ($show_zip)
	$arr['field']['zip'] = array ( 'value' => $client_array['zip'] , 'label' => $label_zip , 'type' => 'input' , 'placeholder' => 'PLZ' ,  'validate' => 'Bitte PLZ angeben' );

if ($setting == 'sign_in')
	$arr['field']['email'] = array ( 'label' => $label_email , 'type' => 'input' , 'placeholder' => $placeholder_email ,  'validate' => 'email' ,  'label_right_class' => "button submit $button_color" ,  'label_right' => $label_right ,  'focus' => $email_focus );

$arr['hidden']["setting" . $camp_key] = $setting;
$arr['hidden']['camp_key'] = $camp_key;
$arr['hidden']['layer_id'] = $layer_id;
$output_form = call_form ( $arr );
$output .= $output_form['html'];
$add_js2 .= $output_form['js'];

// Aufruf für Ajax
echo $output . $add_js2;
