<?php 
session_start();
include ("../../smart_form/include_form.php");

//$arr['form'] = array ( 'id' => 'hideme' );
$arr['form'] = array ( 'id' => 'hideme' ,  'action' => "http://server1.ssi.at/hideme-srv/hideme.fcgi", 'inline' => 'list' );
$arr['ajax'] = array (
		'beforeSend' => "function_hideme_beforeSend()" ,
		'success' => "function_hideme_success(data)",
		'dataType' => 'html' );

$rnd = mt_rand (); // In diesen Verzeichnis wird das Bild abgelegt
//$arr['tab'] = array ( 'tabs' => [ "first" => "Nachricht verstecken" , "sec" => "Nachricht abholen" ] );

if ($_SERVER['SERVER_NAME'] == 'localhost') {
	$upload_dir = "/Applications/XAMPP/xamppfiles/htdocs/center/smart_users/hideme/unpacking/$rnd/";
	$server_name = '';
} 
else { 
	$upload_dir = "/var/www/ssi/smart_users/hideme/unpacking/$rnd/";
	$server_name = 'https://center.ssi.at';
}

if (!is_dir($upload_dir)) { mkdir($upload_dir); }

$arr['field']['img_url'] = array ( 
		'server_name' => $server_name, 
		'mode'=>'single', 
		'type' => 'uploader' , 
		'upload_dir' => "$upload_dir", 
		'upload_url'=>"/smart_users/hideme/unpacking/$rnd/" , 
		'card_class' => 'three', 
		'validate' => 'Bitte ein Bild hochladen',
		'ajax_success' => "$('#key').focus();",
		//'button_upload' => array('text'=>"Geheimes Bild hochladen")
		'button_upload' => 'hidden'
);

//$arr['field']['img_url'] = array ( 'tab' => 'first' , 'label' => 'Bild' , 'type' => 'input' , 'value' => 'http://www.survivaltraining.at/explorer/secret.png',  );
$arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );
$arr['field']['key'] = array ( 'label' => 'Schlüssel eingeben' , 'type' => 'input' , 'value' => '',  'validate' => 'Bitte den Schlüssel eingeben.' );
$arr['field'][] = array ( 'type' => 'div_close');

$arr['hidden']['cmd'] = 'decode';
$arr['hidden']['rnd'] = $rnd;

$arr['button']['submit'] = array ( 'value' => "<i class='icon upload'></i>Geheimbild auspacken" , 'color' => 'green fluid',  'align' =>'center' );
$output_form = call_form ( $arr );

$output .= $output_form['html'];

echo $output;
echo $output_form['js'];
?>