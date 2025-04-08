<?
/*
 * Plattform - Portal
 * Martin Mollay
 * mm@ssi.at 23.01.2011
 */

if ($_GET ['group_id'])
	$_SESSION ['back_group_id'] = $_GET ['group_id'];
if ($_SESSION ['back_group_id'])
	$group_id = $_SESSION ['group_id'] = $_SESSION ['group_default_id'] = $_SESSION ['back_group_id'];

$output .= "
<div id=portal_window></div>
<div id=portal_content></div>
<div id=window_progress></div>
<div id='modal_product_detail' class='large ui modal'>
	<i class='close icon'></i>
	<div class='header' id=modal_header_product_detail>Details</div>
	<div class='content' id=modal_content_product_detail></div>
</div>
<div id='modal_login' class='ui small modal'>
		<i class='close icon'></i>
		<div class='header'>Anmelden</div>
		<div class='content' id=modal_content_login></div>
</div>
<div id='modal_article' class='ui modal'>
		<i class='close icon'></i>
		<div class='header'>Meine Artikel</div>
		<div class='content' id=modal_content_article></div>
</div>
<div id='modal_reg' class='ui modal'>
		<i class='close icon'></i>
		<div class='header'>Registrieren</div>
		<div class='content' id=modal_content_reg></div>
</div>";



//mm@ssi.at wurde deaktiviert - weil es deaktiviert 

// if (! $GLOBALS ['admin_modus']) {
// 	$thema = 'base';	
// 	$GLOBALS ['add_css2'] .="\n<link rel=stylesheet type='text/css' href='../ssi_form2/jquery-ui/css/$thema/jquery-ui.css'>";
// 	$GLOBALS ['add_css2'] .="\n<link rel=stylesheet type='text/css' media='screen' href='../ssi_form2/js/jquery-validate.password/jquery.validate.password.css'>";
// }


// index_admin.php:
// wird bei Syc. umgewandelt in aktuellen Urlnamen - dieser wird für die Rückgabeadresse für Paypal benötigt

$GLOBALS ['add_path_js'] .= "
<script>
var group_id = '$group_id';
var group_default_id = '$group_id';
var url_name = 'change2staticname.html';
var relative_path = 'gadgets/portal/';
</script>";
$GLOBALS ['add_path_js'] .= "<script type='text/javascript' src='gadgets/portal/js/start.js'></script>";

// $GLOBALS['add_js2'] .= "<script type='text/javascript' src='gadgets/portal/js/jquery.tooltip.js'></script>";
// $GLOBALS['add_js2'] .= "<script type='text/javascript' src='gadgets/portal/js/sitemapstyler.js'></script>";
$GLOBALS ['add_css2'] .= "<link rel=stylesheet type='text/css' href='gadgets/portal/css/sitemapstyler.css'>";