<?php
// mm@ssi.at am 04.10.2017
// Ruft Neuen Content von Seite auf, dieser wird mit Ajax übergeben
include_once ('../login/config_main.inc.php');
include_once ('config.inc.php');
$_SESSION['admin_modus'] = true;
$site_id = $_SESSION['site_id'] = $_POST['site_id'];
setcookie ( "site_id", $_SESSION['site_id'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );
setcookie ( "site_id_$page_id", $_SESSION['site_id'], time () + 60 * 60 * 24 * 365, '/', $_SERVER['HTTP_HOST'] );

#include_once ('library/css_umwandler.inc');
include_once ("inc/load_css.php");
include_once ("inc/load_content.php");

$content = load_content ( $site_id, true );
// echo $GLOBALS['add_css2'];
// echo "\n<style type='text/css'>" . css_umwandeln ( $set_style ) . "</style>";
// echo $content;
// echo $GLOBALS['add_path_js'];

//Notwendig damit das Bearbeitungsfeld für das Formalar nach Reload neu geladen werden kann
//$GLOBALS['add_js2'] .= "if ($('#modal_edit_formular').length > 0) $('#modal_edit_formular').remove();";

$GLOBALS['add_js2'] = preg_replace ( "[<script>|</script>]", "", $GLOBALS['add_js2'] );
// echo "\n<script>" . $GLOBALS['add_js2'] . "</script>";

// header ( "Content-Type: application/json", true );
	
$json['add_css2'] = $GLOBALS['add_css2'];
$json['set_style'] = $set_style;
$json['title'] = $_SESSION['site_title'];
$json['favorite'] = true;

$json['public_url'] = call_public_url ( $_SESSION['smart_page_id'],$site_id  );
$json['content'] = $content;
$json['content'] .= $GLOBALS['add_path_js'];
$json['content'] .= "\n<script>" . $GLOBALS['add_js2'] . "</script>";
// $json['add_path_js'] = $GLOBALS['add_path_js'];
// $json['add_js2'] = $GLOBALS['add_js2'];

echo json_encode ( $json );
