<?php
require ("../config.inc.php");

if ($_POST['update_id'])
	$_POST['ID'] = $_POST['update_id'];

if ($_POST['ID'] != 'all' and $_POST['ID']) {
	// Auslesen der Benutzerdaten
	$sql = $GLOBALS['mysqli']->query ( "SELECT *, a.email email, a.company_id company_id, b.email email_client FROM bills a LEFT JOIN client b ON  a.client_id = b.client_id WHERE  bill_id = '{$_POST['ID']}'" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array = mysqli_fetch_array ( $sql );
	$email = $array['email'];
	if ($array['email_client'])
		$email = $array['email_client'];
	
	$bill_number = $array['bill_number'];
	$firstname = $array['firstname'];
	$secondname = $array['secondname'];
	$gender = $array['gender'];
	$title = $array['title'];
	$brutto = $array['brutto'];
	$remind_level = $array['remind_level'];
	$date_create = $array['date_create'];
	$document = $array['document'];
	$company_id = $array['company_id'];
	$add_mysql = "AND company_id  = '$company_id'";
} else {
	// $add_mysql = "AND company_id = '29' ";
	$document = 'rn';
}

if ($firstname or $secondname) {
	if ($gender == 'f')
		$gender = "Sehr geehrte Frau";
	elseif ($gender == 'm')
		$gender = "Sehr geehrter Herr";
	else
		$gender = "Sehr geehrte(r)";
} else
	$gender = "Sehr geehrte Damen und Herren";

$sql2 = $GLOBALS['mysqli']->query ( "SELECT * FROM company WHERE 1 $add_mysql " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$array2 = mysqli_fetch_array ( $sql2 );

// Normales Mail
if (! $remind_level) {
	$subject = $array2[$document . "_send_mail_subject"];
	$message = $array2[$document . "_send_mail"];
	$button_send = 'Dokument absenden';
	$level = 1;
} // Mahnstufe 1
elseif ($_POST['remind'] and ($remind_level == 0 or $remind_level == 1)) {
	$message = $array2['remind_mail1'];
	$subject = $array2['remind_mail_subject1'];
	$button_send = 'Mahnung absenden';
	$level = 2;
} // Mahnstufe 2
elseif ($remind_level == 2) {
	$message = $array2['remind_mail2'];
	$subject = $array2['remind_mail_subject2'];
	$button_send = 'Mahnung absenden';
	$level = 3;
} // Mahnstufe 3 (INKASSO)
elseif ($remind_level >= 3) {
	$message = $array2['remind_mail3'];
	$subject = $array2['remind_mail_subject3'];
	$button_send = 'Mahnung absenden';
	$level = 4;
}

if ($_POST['ID'] != 'all') {
	$subject = preg_replace ( "/\[%bill_number%\]/", $bill_number, $subject );
	$message = preg_replace ( "/\[%bill_number%\]/", $bill_number, $message );
	$message = preg_replace ( "/\[%summery%\]/", nr_format ( $brutto ), $message );
	$message = preg_replace ( "/\[%firstname%\]/", $firstname, $message );
	$message = preg_replace ( "/\[%secondname%\]/", $secondname, $message );
	$message = preg_replace ( "/\[%gender%\]/", $gender, $message );
	$message = preg_replace ( "/\[%title%\]/", $gender, $message );
	$message = preg_replace ( "/\[%date%\]/", $date_create, $message );
}

include ('../../ssi_smart/smart_form/include_form.php');

$arr['ajax'] = array (  'dataType' => "html" ,  'success' => "
	$('#modal_form_send').modal('hide');
	$('#ProzessBarBox').message({ type:'success',title:'Info', text: data });
	table_reload(); " );

$arr['form'] = array ( 'action' => "ajax/form_send2.php" , 'id' => 'form_send' );

// Wird bei Massenversendung nicht angezeigt
if ($_POST['ID'] != 'all' and $_POST['ID']) {
	if (! $_POST['just_send']) {
		$show_manuel_button = true;
	}
	$arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );
	$arr['field']['email'] = array ( 'label_left' => 'An' , 'class' => 'eight wide field' , 'type' => 'input' , 'value' => $email , 'placeholder' => '@email' );
	$arr['field']['email_cc'] = array ( 'label_left' => 'Cc' , 'class' => 'eight wide field' , 'type' => 'input' , 'placeholder' => '@email' );
	$arr['field'][] = array ( 'type' => 'div_close' );
}

$arr['field']['subject'] = array ( 'label_left' => 'Betreff' , 'type' => 'input' , 'value' => $subject );

$arr['field']['message2'] = array ( 'type' => 'textarea' , 'value' => $message , 'rows' => 20 );
$arr['hidden']['remind_level'] = $level;
$arr['hidden']['just_send'] = $_POST['just_send'];
// $arr['hidden']['id'] = $_POST['ID'];
// $arr['hidden']['bill_id'] = $_POST['ID'];
$arr['field']['bill_id'] = array ( 'type' => 'hidden' , 'value' => $_POST['ID'] );
$arr['button']['submit'] = array ( 'value' => $button_send , 'color' => 'green' , 'icon' => 'send' );

if ($document == 'rn' and $show_manuel_button)
	$arr['button']['manuell'] = array ( 'value' => 'Als versendet setzen' , 'color' => 'grey' , 'tooltip' => 'Bei Klick wird keine Mail verschickt aber die Mahn-Stufe im System gesetzt' ,  'js' => "set_mahung('{$_POST['ID']}')" );

$arr['button']['close'] = array ( 'value' => 'Abbrechen' ,  'js' => "$('#modal_form_send').modal('hide');" );
$output = call_form ( $arr );

echo $output['html'];
echo "<script type=\"text/javascript\" src=\"js/send_form.js\"></script>";
echo $output['js'];
