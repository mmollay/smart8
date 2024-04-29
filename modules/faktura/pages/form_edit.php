<?php
/*
 * page_contact.php - SSI NEWSLETTER: SEMANTICU - UI
 * @author Martin Mollay
 * @last-changed 2107-02-20
 */
include (__DIR__ . '/../f_config.php');
include (__DIR__ . '/../../../../smartform/include_form.php');

// read company_IDs
$sql_company = $GLOBALS['mysqli']->query("SELECT company_id, company_1 FROM company where user_id = '{$_SESSION['user_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
while ($sql_array = mysqli_fetch_array($sql_company)) {
	$arr_comp[$sql_array['company_id']] = $sql_array['company_1'] . "(ID:{$sql_array['company_id']})</option>";
}

$arr['form'] = array('action' => "ajax/form_edit2.php", 'id' => 'form_edit', 'size' => 'small', 'inline' => 'list');

switch ($_POST['list_id']) {

	// Rechnung bearbeiten
	case 'elba_list':
		include ('../form/f_elba.php');
		break;
	//Eingabefeld für elba->zum anlegen weiterer Automatoren
	case 'add_automator_form':
		include ('../form/f_automator.php');
		$arr['ajax'] = array('dataType' => "script");
		$arr['form'] = array('action' => "ajax/form_automator2.php", 'id' => 'form_automator', 'size' => 'small', 'inline' => 'list');
		$arr['button']['submit'] = array('value' => 'Speichern', 'color' => 'green', 'id' => '');
		$arr['button']['close'] = array('value' => 'Schließen', 'color' => 'gray', 'js' => "$('#modal_form_automator_edit').modal('hide'); $('.ui.modal#modal_form_automator_edit>.content').empty();");
		break;

	case 'automator_list':

		$success = "if ( data  == 'ok' ) { 
        $('body').toast({message: 'Eine neue Ausgabe wurde gespeichert'});
		table_reload();
        $('#modal_form_edit').modal('hide'); }";
		$arr['ajax'] = array('success' => $success, 'dataType' => "html");
		include ('../form/f_automator.php');
		break;

	case 'bill_list':
		include ('../form/f_earning.php');
		break;

	case 'group_list':
		include ('../form/f_group.php');
		break;

	case 'client_list':
		include ('../form/f_client.php');
		break;

	case 'client_oegt_list':

		include ('../form/f_client.php');
		break;

	case 'accountgroup_in_list':
	case 'accountgroup_out_list':
		include ('../form/f_accountgroup.php');
		break;

	case 'option_list':
		include ('../form/f_option.php');
		break;

	case 'account_out_list':
	case 'account_in_list':
		include ('../form/f_account.php');
		break;

	case 'issues_list':
		include ('../form/f_issues.php');
		break;

	case 'article_admin_list':
		include ('../form/f_article.php');
		break;
}


if ($_POST['list_id'] != 'client_oegt_list')
	if ($_POST['list_id'] != 'bill_list' and $_POST['list_id'] != 'issues_list') {
		$arr['button']['submit'] = array('value' => 'Speichern', 'color' => 'blue');
		$arr['button']['close'] = array('value' => 'Schließen', 'color' => 'gray', 'js' => "$('#modal_form, #modal_form_edit, #modal_form_clone, #modal_form_new').modal('hide'); $('.ui.modal>.content').empty();");
	}


//$arr ['button'] ['submit'] = array ('value' => 'Speichern','color' => 'blue' );
//$arr ['button'] ['close'] = array ('value' => 'Schließen','color' => 'gray','js' => "$('#modal_form, #modal_form_edit, #modal_form_clone, #modal_form_new').modal('hide'); $('.ui.modal>.content').empty();" );

// $arr['hidden']['update_id'] = $_POST['update_id'];
$arr['hidden']['list_id'] = $_POST['list_id'];
$output = call_form($arr);

echo $output['html'];
echo "<div class='ui group popup top left hidden fluid' id='popup_group_container'>Neue Firma anlegen:<div class='ui action input fluid'><input id='input_group' type='text'></div></div>";
echo "<script type=\"text/javascript\">var company_id = {$_SESSION['faktura_company_id']}; </script>";
echo $output['js'];
echo $add_js;

// $arr ['button'] ['close'] = array ('value' => 'Schließen','color' => 'gray','js' => "$('#modal_form, #modal_form_edit, #modal_form_clone, #modal_form_new').modal('hide'); $('.ui.modal>.content').empty();" );
// $arr ['button'] ['submit'] = array ('value' => 'Speichern','color' => 'blue' );
