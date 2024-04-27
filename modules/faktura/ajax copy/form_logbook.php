<?php
require ("../config.inc.php");
include ('../../ssi_smart/smart_form/include_form.php');

$arr['ajax'] = array (  'dataType' => "html" ,  'success' => "
$('#modal_logbook').modal('hide');
$('#ProzessBarBox').message({ type:'success',title:'Info', text: data });" );

$arr['form'] = array ( 'action' => "ajax/form_logbook2.php" , 'id' => 'form_logbook' );

$arr['field']['message1'] = array ( 'type' => 'textarea' , 'value' => $message ,  'focus' => true  );
$arr['hidden']['bill_id'] = $_POST['update_id'];

$arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'green' , 'icon' => 'save' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' ,  'js' => "$('#modal_logbook').modal('hide');" );
$output = call_form ( $arr );

echo $output['html'];
echo $output['js'];

$query = $GLOBALS['mysqli']->query ( "select * from logfile WHERE bill_id = '{$_POST['update_id']}' order by time_stamp desc" );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$message = nl2br ( utf8_encode ( $array['message'] ) );
	echo "<table class='ui very compact small table'>
	<thead>
	<tr class=logfiles_head><th>{$array['info']}</td><th class='right aligned'>{$array['time_stamp']}</th></tr>
	</thead>
	<tbody>
	<tr><td colspan=2 class=logfiles_body>$message</td></tr>
	<tbody>	
	</table>";
}

?>