<?
session_start ();

$layer_id = $_POST['layer_id'];
include (__DIR__ . "/../../gadgets/config.php");
include (__DIR__ . "/../../smart_form/include_form.php");
include ("function.php");
include_once ('../function.inc.php');
call_layer_parameter ( $layer_id );

if (! $send_button)
	$send_button = 'Nachricht senden';

$arr['form'] = array ( 'id' => "form_formular$layer_id" , 'class' => "$segment_size fomular" , 'inline' => 'true' , 'action' => 'gadgets/formular/submit.php' );
$arr['ajax'] = array ( 'datatype' => 'script' );
$arr['field']["left_{$layer_id}_0"] = array ( 'type' => 'div' , 'class' => 'sortable_formular' );

// Auslesen der Felder
$query = $GLOBALS['mysqli']->query ( "SELECT * from {$_SESSION['db_smartkit']}.smart_formular WHERE layer_id = '$layer_id' ORDER by sort" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
while ( $array2 = mysqli_fetch_array ( $query ) ) {
	// $arr['field'][$array2['field_id']] = show_field ( $array2['field_id'] );
	include (__DIR__ . '/include_field.php');
}

$arr['field'][] = array ( 'type' => 'content' , 'value' => '<br>' );

$arr['field'][] = array ( 'type' => 'div_close' );
$arr['hidden']['layer_id'] = $layer_id;
//$arr['hidden']['from_id'] = $from_id;
//$arr['hidden']['feedback_id'] = $feedback_id;
//$arr['hidden']['camp_key'] = $camp_key;

// if ($recaptcha)
// $arr['field'][] = array ( 'type' => 'recaptcha' , 'key' => "{$_SESSION['site_key']}" ); // siehe config.php

if (! $button_color)
	$button_color = 'green';
if ($button_icon)
	$add_button_icon = "<i class='icon $button_icon'></i>";
$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => "$add_button_icon$send_button" ,  'color' => $button_color , 'class' => "$segment_size" );

$output_form = call_form ( $arr );
$add_js2 .= $output_form['js'];

if ($_SESSION['admin_modus'])
	$output .= "<div class='admin_form_field'>";

$output .= "<div class='ui basic segment' id='context_form$layer_id'>";

if ($_SESSION['admin_modus']) {
	$output .= "<div class='ui sticky' id = 'sticky$layer_id'>";
	$output .= "
	<div id = 'form_button_feedback$layer_id'  class='ui mini compact menu' >
	<div style='cursor:move' class='new_form_field item tooltip' title='Inputfeld hineinziehen' id='input' >Input</div>
	<div style='cursor:move' class='new_form_field item tooltip' title='Dropdownfeld hineinziehen' id='select'>Select</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='radio' title='Radiobuttons hineinziehen'>Radio</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='checkbox' title='Checkbox hineinziehen'>Checkbox</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='textarea' title='Eingabefeld hineinziehen'>Textarea</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='text' title='Textfeld hineinziehen' >Text</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='uploader' title='Uploader hineinziehen' >Upload</div>
	<div style='cursor:move' class='new_form_field item tooltip' id='slider' title='Slider hineinziehen' >Slider</div>
	</div>";
	$output .= "</div>";
}

if ($_SESSION['admin_modus']) {
	$output .= "\n<script>appendScript('gadgets/formular/admin/jquery-quickedit.js');</script>";
	$output .= "\n<script>appendScript('gadgets/formular/admin/main.js');</script>";
	$output .= "\n<script>load_edit_formular('$layer_id');</script>";
	// if ($_POST[ajax]) {
	// $output .= "\n<script>load_edit_formular('$layer_id');</script>";
	// } else {
	$output .= "\n<script>$(window).load(function() { load_edit_formular('$layer_id');  });</script>";
	// }
}

$output .= $output_form['html'];
$add_js2 .= $output_form['js'];

if ($_SESSION['admin_modus'])
	$output .= "</div>";

$output .= "</div>";

//$output_form = call_form ( $arr );


// Aufruf für Ajax
echo $output . $add_js2;