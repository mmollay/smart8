<?php

if ($_POST['list_id'] == 'account_out_list')
	$_POST['option'] = 'out';
elseif ($_POST['list_id'] == 'account_in_list')
	$_POST['option'] = 'in';

// Auflistung der Gruppenkonten
$sql_accountgroup = $GLOBALS['mysqli']->query ( "SELECT accountgroup_id,title FROM accountgroup where user_id = '{$_SESSION['user_id']}' and `option` = '{$_POST['option']}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
while ( $sql_array = mysqli_fetch_array ( $sql_accountgroup ) ) {
	$array_accountgroup[$sql_array['accountgroup_id']] = $sql_array['title'];
}

$tax_array = array ( '0' => "0 %" , '10' => '10 %' , '20' => '20 %' );
$option_array = array ( 'in' => 'Eingangsrechnung' , 'out' => 'Ausgangsrechnung' );

if ($_POST['update_id']) {
	$arr['sql'] = array ( 'query' => "SELECT * from accounts WHERE account_id = '{$_POST['update_id']}'" );
	
	$sql_check = $GLOBALS['mysqli']->query ( "SELECT * FROM accounts INNER JOIN issues ON account = account_id where account_id = {$_POST['update_id']} " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$set_lock = mysqli_num_rows ( $sql_check );
}

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );
$arr['field']['title'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' ,  'validate' => true , 'class' => 'nine wide' );
$arr['field']['code'] = array ( 'type' => 'input' , 'label' => 'Code' ,  'label_right_class' => 'button' ,  'label_right' => '<a href="download/Kontenplan.pdf" target="neu" >Kontenplan</a>' );
$arr['field'][] = array ( 'type' => 'div_close' );

if (! $set_lock) {
	$arr['field']['tax'] = array ( 'type' => 'dropdown' , 'label' => 'Mwst.' , 'array' => $tax_array ,  'validate' => true );
} else
	$arr['field']['tax'] = array ( 'type' => 'input' , 'label' => 'Mwst.' ,  'label_right' => '%' , 'disabled' => true );

$arr['field']['afa_400'] = array ( 'type' => 'checkbox' , 'label' => 'Afa > 400 &euro;' , 'info' => 'Wirtschaftsgüter über 400 Euro' );
if (! $set_lock) {
	$arr['field']['option'] = array ( 'type' => 'dropdown' , 'label' => 'Option' , 'array' => $option_array , 'value' => $_POST['option']);
}
// $arr['field']['company_id'] = array ( 'type' => 'dropdown' , 'label' => 'Firma' , 'array' => $arr_comp , 'class' => 'search' );
$arr['field']['accountgroup_id'] = array ( 'type' => 'dropdown' , 'label' => 'Gruppe' , 'array' => $array_accountgroup, 'clear' => true  );
if ($_POST['update_id'])
	$arr['hidden']['account_id'] = $_POST['update_id'];

$arr['ajax'] = array (  'success' => "$('#modal_form').modal('hide'); table_reload();" ,  'dataType' => "html" );