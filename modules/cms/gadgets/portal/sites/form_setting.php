<?php
$set_TrackingCode = true;
include_once ("../login.php");
include_once ("../../../smart_form/include_form.php");

if (! $_SESSION['client_user_id']) {
	echo "User existiert nicht";
} else {
	$arr['sql'] = array ( 'query' => "SELECT * from client WHERE client_id = '{$_SESSION['client_user_id']}'" );
}

$arr['form'] = array ( 'id' => 'form_new_account' , 'width' => '100%' , 'align' => 'center' , 'action' => "$relative_path" . 'inc/setting_save.php' , 'size' => '' , 'class' => '' , 'inline' => 'false' );
$arr['ajax'] = array ( 'success' => "if ( data == 'ok') { $('#modal_reg').modal('hide'); } else alert('Error'); " , 'datatype' => 'html' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'three fields' );
$arr['field']['gender'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'label' => 'Anrede' , 'array' => array ( 'f' => 'Frau' , 'm' => 'Herr' ) , 'class' => 'four wide' ,  'validate' => 'Bitte Anrede auswählen' );
$arr['field']['firstname'] = array ( 'tab' => 'first' , 'label' => 'Vorname' , 'type' => 'input' ,  'validate' => 'Bitte Vornamen angeben' , 'class' => 'wide seven' );
$arr['field']['secondname'] = array ( 'tab' => 'first' , 'label' => 'Nachname' , 'type' => 'input' ,  'validate' => 'Bitte Nachnamen angeben' , 'class' => 'wide six' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['company_1'] = array ( 'tab' => 'first' , 'label' => 'Firma' , 'type' => 'input' );
$arr['field']['street'] = array ( 'label' => 'Strasse' , 'type' => 'input' , 'placeholder' => 'Strasse' ,  'validate' => 'Bitte Strasse eingeben' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields' );
$arr['field']['zip'] = array ( 'tab' => 'first' , 'label' => 'Plz' , 'type' => 'input' , 'class' => 'two wide' ,  'validate' => 'Bitte Plz angeben' );
$arr['field']['city'] = array ( 'tab' => 'first' , 'label' => 'Ort' , 'type' => 'input' , 'class' => 'seven wide' ,  'validate' => 'Bitte Ort angeben' );
$arr['field']['country'] = array ( 'tab' => 'first' , 'label' => 'Land' , 'array' => 'country' , 'type' => 'dropdown' , 'class' => 'seven wide' ,  'validate' => 'Bitte Land angeben' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['mobil'] = array ( 'tab' => 'first' , 'label' => 'Telefon' , 'type' => 'input' );

$arr['field']['email'] = array ( 'label' => 'Email' , 'class' => 'disabled' , 'type' => 'input' , 'placeholder' => 'email' ,  'validate' => 'email' ,  'label_right' => '(ist auch Username)' );

$arr['field']['password'] = array ( 'label' => 'Passwort' , 'type' => 'password' );
$arr['field']['newsletter'] = array ( 'type' => 'checkbox' , 'label' => "$strNewsletterSubcribe" , 'value' => 1 );

$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => "$strButtonSaveSetting" , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('#modal_reg').modal('hide'); " );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];
exit ();