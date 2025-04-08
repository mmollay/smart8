<?php
include ('../../../../login/config_main.inc.php');
include_once ("../../../smart_form/include_form.php");

// $layer_id = $_POST['layer_id'];
$array_position = preg_split ( "/_/", $_POST['layer_id'] );
$layer_id = $array_position['1'];

$company_id = $_SESSION['company_id'];
$type = $_POST['type'];

if ($type == 'select' or $type == 'radio') {
	$default_array = array ( '1' => 'Wert 1' , '2' => 'Wert 2' );
	$default_json_array = json_encode ( $default_array );
}

$type_label['checkbox'] = 'Checkbox';
$type_label['toggle'] = 'Toggle';
$type_label['radio'] = 'Radio-Buttons';
$type_label['select'] = 'Selectfeld';
$type_label['textarea'] = 'Textfeld';
$type_label['input'] = 'Eingabefeld';

if ($type == 'text') {
	$text = 'Klicke auf diesen Text um in zu verändern.';
}

// erzeugt Feld in der Datenbank
$GLOBALS['mysqli']->query ( "INSERT INTO {$_SESSION['db_smartkit']}.smart_formular SET
type = '$type',
layer_id = '$layer_id',
label = '{$type_label[$type]}',
value = '$default_json_array',
text = '$text',
help = '',
placeholder = '',
validate = '',
setting_array = '',
set_email = 0,
newsletter_field  = '',
default_value = '',
position = '',
splitter_field_id = 0,
sort = 0
" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$id = mysqli_insert_id ( $GLOBALS['mysqli'] );
set_update_site ();
if ($type == 'text') {
	if ($_SESSION['admin_modus'])
		$setting = "contenteditable='true'";
	$arr['field'][$id] = array ( 'class_content' => 'cktext' , 'setting' => $setting , 'type' => 'content' ,  'text' => $text );
} elseif ($type == 'uploader') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => 'dropdown' , 'array' => $default_array , 'placeholder' => '--bitte wählen--' );
} elseif ($type == 'select') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => 'dropdown' , 'array' => $default_array , 'placeholder' => '--bitte wählen--' );
} elseif ($type == 'slider') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => 'slider' ,  'max' => 10, 'class' => 'labeled ticked red', );
} elseif ($type == 'checkbox' or $type == 'toggle') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => $type );
} elseif ($type == 'radio') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => 'radio' , 'array' => $default_array );
} elseif ($type == 'textarea') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => 'textarea' );
	$value = '';
} elseif ($type == 'input') {
	$arr['field'][$id] = array ( 'label_class' => 'formular' , 'label' => $type_label[$type] , 'type' => $type );
	$value = '';
} elseif ($type == 'splitter') {
	$splitter .= "
	<div class='ui two stackable grid'>
	<div class='row'>
	<div id='left' class='wide column smart_content_container sortable'>test</div>
	<div id='right' class='wide column smart_content_container sortable'>test</div>
	</div>
	</div>";
	$arr['field'][$id] = array ( 'type' => 'content' ,  'text' => $splitter );
}

$output_form = call_form ( $arr );
echo $output_form['html'];
echo $output_form['js'];
