<?
include_once '../config.inc.php';
include ('../../ssi_smart/smart_form/include_form.php');

$arr['form'] = array ( 'action' => "ajax/form_print2.php" , 'id' => 'form_print' , 'size' => 'small' , 'inline' => 'list' );
$arr['ajax'] = array (  'success' => "$('#modal_form_print').modal('hide'); table_reload();" ,  'dataType' => "html" );

$update_id = $_POST['update_id'];
if ($update_id)
	$arr['hidden']['bill'] = $update_id;
else if ($_GET['all'])
	$arr['hidden']['bill'] = 'all';

$arr['button']['submit'] = array ( 'value' => 'Ja' , 'color' => 'green' );
$arr['button']['close'] = array ( 'value' => 'Nein' , 'color' => 'gray' ,  'js' => "$('#modal_form_print').modal('hide');" );
$output = call_form ( $arr );

echo $output['html'];
echo $output['js'];
