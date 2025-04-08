<?php
/*
 * @author Martin Mollay
 * @last-changed 2017-04-13
 */
include_once ('../../../login/config_main.inc.php');
include ('../../smart_form/include_form.php');
include ('../rights.inc.php');

// Defaultwerte
$send_button ['text'] = 'Speichern';
$send_button ['color'] = 'blue';
$send_button ['icon'] = 'save';

$array_sites = GenerateArraySql ( "SELECT site_id, title, if (menu_text != title OR menu_text = '' , CONCAT (' [',menu_text,']'),'') menu_text FROM smart_langSite INNER JOIN smart_id_site2id_page ON smart_langSite.fk_id = smart_id_site2id_page.site_id and lang='{$_SESSION['page_lang']}' AND page_id='{$_SESSION['smart_page_id']}'", '%title% %menu_text%' ); // %var% die ausgegeben werden soll

// Seite klonen
if ($_POST ['clone_id']) {
	// Verwendet die Session_ID, da Seiten zum Teil über AJAX geladen werden und daher der Clonebutton nicht ausgetauscht wird
	$_POST ['update_id'] = $_POST ['clone_id'] = $_SESSION ['site_id'];
}

switch ($_POST ['list_id']) {

	case 'global_option' :
		include ('../form/f_option.php');
		break;
	case 'site_list' :
	case 'site_form' :
		include ('../form/f_site.php');
		breaK;
}

// $arr['field']['split_representation'] = array('type'=>'hidden');
$arr ['hidden'] ['list_id'] = $_POST ['list_id'];
$arr ['hidden'] ['update_id'] = $_POST ['update_id'];
$arr ['button'] ['submit'] = array ('value' => $send_button ['text'],'color' => $send_button ['color'],'icon' => $send_button ['icon'] );
$arr ['button'] ['close'] = array ('value' => 'Abbrechen','color' => 'gray','js' => "$('#edit_field, #option_global, #option_site, #modal_form').modal('hide');" );
$output = call_form ( $arr );
echo $output ['html'];
echo $add_js;
echo $output ['js'];