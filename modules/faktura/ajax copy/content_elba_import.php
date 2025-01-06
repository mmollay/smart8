<?php
session_start ();
require_once ('../config.inc.php');
include ('../../ssi_smart/smart_form/include_form.php');

if ($_SERVER ['SERVER_NAME'] == 'localhost') {
	$upload_dir = "/Applications/XAMPP/xamppfiles/htdocs/smart_users/faktura/";
	$updoad_url = "/smart_users/faktura/";
	$server_name = '';
} else {
	$upload_dir = "/var/www/ssi/smart_users/faktura/";
	$updoad_url = "/smart_users/faktura/";
	$server_name = 'https://center.ssi.at';
}

$array_elba_import = array ("email" => "Email","firstname" => "Vorname","secondname" => "Nachnname","gender" => "Gender","title" => "Title" );

foreach ( $array_elba_import as $key => $text ) {
	$label_list .= "<div class='label basic ui'>$text</div>";
}

$arr ['header'] = array ('text' => "<div class='content ui header small orange'><i class='icons'><i class='icon database'></i><i class='corner add icon'></i></i> Elba importieren</div>",'segment_class' => 'message attached' );

$arr ['form'] = array ('action' => "ajax/content_elba_import2.php",'id' => 'form_edit','width' => '800','class' => 'segment attached','size' => 'small' );

$arr ['ajax'] = array ('onLoad' => $onload,'success' => "$('#modal_msg>.content').html(data); $('#modal_msg').modal('show');",'dataType' => "html" );

$arr ['field'] ['accountnumber'] = array ('type' => 'dropdown','array' => $array_account_number,'label' => 'Konto','validate' => true,'placeholder' => 'Kontonummer wählen' );

$arr ['field'] [] = array ('id' => 'remove_list','type' => 'checkbox','label' => "Löschen und neu schreiben","info" => "" );

// $arr['field'][] = array(
//     'type' => 'div',
//     'class' => 'ui message'
// );

// $arr['field'][] = array(
//     'type' => 'accordion',
//     'class' => 'styled fluid',
//     'title' => 'Textdatei',
//     'active' => true
// );

$arr ['field'] ['file_data'] = array ('server_name' => $server_name,'button_upload' => "Datei auswählen (*.txt, *.csv)",'accept' => array ('csv','txt' ),'mode' => 'single','type' => 'uploader','upload_dir' => "$upload_dir",'upload_url' => "$updoad_url",'card_class' => 'one' );

// $arr['field'][] = array(
//     'type' => 'accordion',
//     'title' => 'Templates',
//     'split' => true
// );

// $arr['field']['setTEXT'] = array(
//     'type' => 'textarea',
//     'rows' => '15',
//     'value' => $liste
// );

// $arr['field'][] = array(
//     'type' => 'content',
//     'text' => "<b>Reihenfolge:</b> datum; beschreibung; buchunsdatum; betrag; einheit; Zeitstempel;"
// );

// $arr['field']['setDelimiter'] = array(
//     'type' => 'input',
//     // 'label' => 'Trennzeichen',
//     'label_right' => 'Trennzeichen (;,[tab],#)',
//     'class' => 'six wide',
//     'value' => ';'
// );

// $arr['field'][] = array(
//     'type' => 'accordion',
//     'close' => true
// );

// $arr['field'][] = array(
//     'type' => 'div_close'
// );

// $arr['field'][] = array(
// 'type' => 'content',
// 'class' => 'message ui',
// 'text' => "In dieser Reihenfolge in der Liste angeben angeben:<br>$label_list"
// );

$arr ['button'] ['submit'] = array ('value' => 'Daten importieren','icon' => 'send','color' => 'blue' );
$arr_output = call_form ( $arr );

$content = $arr_output ['html'];
$content .= "<div id=dialog_msg></div>";
$content .= $arr_output ['js'];

$content .= "
<div class='ui small modal' id='modal_msg'>
	<div class='header'>Info</div>
	<div class='content'><p></p><p></p><p></p></div>
	<div class='actions'>
		<div class='ui cancel button' >Schließen</div>
	</div>
</div>";

echo $content;

?>