<?php
session_start ();
include ("../../smart_form/include_form.php");

// $arr['form'] = array ( 'id' => 'hideme' );
$arr['form'] = array ( 'id' => 'hideme' , 'action' => "https://hideme.ssi.at/cgi-bin/hideme.fcgi" , 'inline' => 'list' );
$arr['ajax'] = array ( 'beforeSend' => "function_hideme_beforeSend()" , 'success' => "function_hideme_success(data)" , 'dataType' => 'html' );

$rnd = mt_rand (); // In diesen Verzeichnis wird das Bild abgelegt
                   // $arr['tab'] = array ( 'tabs' => [ "first" => "Nachricht verstecken" , "sec" => "Nachricht abholen" ] );

if ($_SERVER['SERVER_NAME'] == 'localhost') {
	$upload_dir = "/Applications/XAMPP/xamppfiles/htdocs/smart_users/hideme/produce/$rnd/";
	$updoad_url = "/smart_users/hideme/produce/$rnd/";
	$server_name = '';
} else {
	$upload_dir = "/var/www/ssi/smart_users/hideme/produce/$rnd/";
	$updoad_url = "/smart_users/hideme/produce/$rnd/";
	$server_name = 'https://center.ssi.at';
}

if (! is_dir ( $upload_dir )) {
	mkdir ( $upload_dir );
}

$ck_editor = "resize_enabled : false,autoDetectPasteFromWord: true,pasteFromWordRemoveStyles: true,filebrowserBrowseUrl : '../ssi_smart/admin/ckeditor_link.php', height:'194px',removePlugins : 'elementspath,resize,autogrow'";

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );

$arr['field']['img_url'] = array ( 'class' => 'four wide field' ,
		'label' => '1.Bild hochladen' ,
		'server_name' => $server_name ,
		'mode' => 'single' ,
		'type' => 'uploader' ,
		'upload_dir' => "$updoad_url" ,
		'upload_url' => "/smart_users/hideme/produce/$rnd/" ,
		'validate' => 'Bitte ein Bild hochladen' ,
		'ajax_success' => "$('#key').focus();" ,
		'options' => 'imageMaxWidth:1000,imageMaxHeight:1000' ,
		// 'button_upload' => array('text'=>"Foto auswählen", 'color' => 'blue', 'icon' => 'upload' )
		'button_upload' => 'hidden' ,
		'dropzone' => array ( 'style' => 'padding-top:25px; padding-bottom:25px;' ) ,
		'card_class' => 'one' );

$arr['field']['msg'] = array ( 'class' => 'twelve wide field' , 'rows' => 12 , 'label' => '2.Nachricht schreiben' , 
		// 'type' => 'ckeditor' ,
		'type' => 'textarea' , 
		// 'config' => $ck_editor ,
		'toolbar' =>'mini' ,  'validate' => 'Bitte eine gewünschte Nachricht eingeben.' , 'placeholder' => 'Gebe deine geheime Botschaft ein...' ,  'focus' => true );

$arr['field'][] = array ( 'type' => 'div_close' );

// $arr['field']['img_url'] = array ( 'tab' => 'first' , 'label' => 'Bild' , 'type' => 'input' , 'value' => 'http://giraph.com/images/qsets/icon38.jpg', );
$arr['field'][] = array ( 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['key'] = array ( 'label' => '3.Schlüssel vergeben' , 'type' => 'input' , 'class' => 'field' , 'value' => '' ,  'validate' => 'Bitte einen Schlüssel vergeben.' , 'placeholder' => 'geheimwort' );
$arr['field']['img_name'] = array ( 'label' => 'Bildname (optional)' , 'type' => 'input' , 'class' => 'field' , 'value' => 'hide_image' ,  'label_right' => '.png' , 'info' => 'Dieser Name wird beim Speichern des neuen Bildes verwendet' ,  'validate' => true );
$arr['field'][] = array ( 'type' => 'div_close' );
$arr['hidden']['cmd'] = 'encode';
$arr['hidden']['out'] = 'base64';
$arr['hidden']['rnd'] = $rnd;

$arr['button']['submit'] = array ( 'value' => "<i class='icon download'></i> Geheimbild erstellen" , 'class' => 'green fluid' , 'align' => 'center');
// $arr['button']['reset'] = array ( 'value' => 'Zurücksetzen' ,  'js' => "$('#name').focus();" );
$output_form = call_form ( $arr );

// $add_js2 = $output_form['js'];
// $add_js2 .= "<script type='text/javascript' src='gadgets/hideme/hideme.js'></script>";
if ($_SESSION['admin_modus']) {
	$add_path_js .= "\n<script type='text/javascript' src='smart_form/ckeditor/ckeditor.js'>;";
	$add_path_js .= "\n<script type='text/javascript' src='smart_form/ckeditor/adapters/jquery.js'></script>";
}
$output .= $output_form['html'];

echo $output;
echo $output_form['js'];
?>