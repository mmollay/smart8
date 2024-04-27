<?php
require ("../config.inc.php");
include ('../../ssi_smart/smart_form/include_form.php');

$arr['ajax'] = array (  'dataType' => "html" ,  'success' => "
	$('.modal-booking').modal('hide');
	$('#ProzessBarBox').message({ type:'success',title:'Info', text: 'Rechnung \"'+ data +'\" verbucht' });
	table_reload();
" );

$arr['form'] = array ( 'action' => "ajax/book_bill2.php" , 'id' => 'form_edit' , 'size' => 'small' , 'inline' => 'list' );

/*
 * Bei AUSGABEN
 */
if ($_POST['option'] == 'issue') {
	$arr['field']['date_booking_value'] = array ( 'label' => 'Buchungsdatum' , 'type' => 'date' , 'value' => 'date' ( 'Y-m-d' ) ,  'validate' => true );
	$arr['hidden']['issue_id'] = $_POST['update_id'];
}
/*
 * Bei Einnahmen
 */
else {
	// Betrag auslesen
	$sql = $GLOBALS['mysqli']->query ( "SELECT brutto,bill_number FROM bills WHERE bill_id = '{$_POST['update_id']}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array = mysqli_fetch_array ( $sql );
	$booking_total = nr_format ( $array['brutto'] );
	$bill_number = $array['bill_number'];
	
	// Buchungsmaske
	$arr['field'][] = array ( 'type' => 'header' ,  'text' => "Rechnungnsnummer: $bill_number" );
	$arr['field']['date_booking'] = array ( 'label' => 'Buchungsdatum' , 'type' => 'date' , 'value' => 'date' ( 'Y-m-d' ) ,  'validate' => true );
	$arr['field']['booking_total'] = array ( 'label' => 'Buchungsbetrag' , 'type' => 'input' , 'value' => $booking_total ,  'validate' => true );
	$arr['field']['booking_command'] = array ( 'label' => 'Buchungs-Kommentar' , 'type' => 'textarea' );
	$arr['hidden']['bill_id'] = $_POST['update_id'];
}

$arr['button']['submit'] = array ( 'value' => 'Verbuchen' , 'color' => 'green' , 'icon' => 'lock' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('.modal-booking').modal('hide');" );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];
echo "<script type=\"text/javascript\" src=\"js/send_form.js\"></script>";
