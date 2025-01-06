<?php
include (__DIR__ . '/../f_config.php');
include (__DIR__ . '/../../../smartform/include_form.php');

if (!$_POST['delete_id']) {
	$arr['ajax'] = array('success' => "table_reload(); $('#modal_form_delete').modal('hide');", 'dataType' => "html");
	$arr['hidden']['delete_id'] = $_POST['update_id'];
	$arr['hidden']['list_id'] = $_POST['list_id'];
	// $arr['field'][] = array ( 'id' => 'password' , 'label' => 'Passwort' , 'type' => 'password' , 'placeholder' => 'Passwort' , 'validate' => true , 'focus' => true );
	$arr['button']['submit'] = array('value' => 'Löschen', 'color' => 'red');
	$arr['button']['close'] = array('value' => 'Abbrechen', 'color' => 'gray', 'js' => "$('#modal_form_delete').modal('hide'); ");
	$output = call_form($arr);
	echo $output['html'];
	echo $output['js'];
	exit();
}

// Password muss stimmen damit die Daten geloescht werden können
// if ($_POST['password'] != $superuser_passwd) return;

$explode = explode(',', $_POST['delete_id']);

switch ($_POST['list_id']) {
	case 'option_list':
		//TODO: Filter nur löschbar wenn noch keine Einträge für dieses Unternehmen gemacht wurden;

		//$GLOBALS ['mysqli']->query ( "DELETE FROM company WHERE company_id = '{$_POST['delete_id']}' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		break;

	case 'automator_list':
		$GLOBALS['mysqli']->query("DELETE FROM automator WHERE automator_id = '{$_POST['delete_id']}' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));
		break;

	case 'client_list':
		foreach ($explode as $_POST['delete_id']) {
			$sql = "DELETE FROM client WHERE client_id = '{$_POST['delete_id']}' LIMIT 1 ";
			$GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));
		}
		break;

	case 'group_list':
		$GLOBALS['mysqli']->query("DELETE FROM article_group  WHERE group_id = '{$_POST['delete_id']}' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));
		break;
	case 'accountgroup_out_list':
	case 'accountgroup_in_list':
		$GLOBALS['mysqli']->query("DELETE FROM accountgroup  WHERE accountgroup_id = '{$_POST['delete_id']}' LIMIT 1 ") or die(mysqli_error($GLOBALS['mysqli']));
		break;
	case 'account_out_list':
	case 'account_in_list':
		$query = $GLOBALS['mysqli']->query("SELECT SUM(brutto) FROM issues WHERE 1 and account = '{$_POST['delete_id']}'  LIMIT 1");
		$array = mysqli_fetch_array($query);
		if (!$array[0]) {
			$GLOBALS['mysqli']->query("DELETE FROM accounts where account_id = '{$_POST['delete_id']}'  LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
		}
		break;
	case 'issues_list':
		foreach ($explode as $_POST['delete_id']) {
			$GLOBALS['mysqli']->query("DELETE FROM issues where bill_id = '{$_POST['delete_id']}'  LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
			$GLOBALS['mysqli']->query("UPDATE data_elba SET connect_id = '' where connect_id = '{$_POST['delete_id']}' LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
		}
		break;
	case 'bill_list':
		$GLOBALS['mysqli']->query("DELETE FROM bills where bill_id = '{$_POST['delete_id']}'  LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
		$GLOBALS['mysqli']->query("DELETE from bill_details WHERE bill_id = '{$_POST['delete_id']}'") or die(mysqli_error($GLOBALS['mysqli']));
		break;
	case 'article_admin_list':
		$GLOBALS['mysqli']->query("DELETE FROM article_temp where temp_id = '{$_POST['delete_id']}'  LIMIT 1") or die(mysqli_error($GLOBALS['mysqli']));
		break;
}