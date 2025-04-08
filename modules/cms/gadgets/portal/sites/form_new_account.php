<?php


$set_TrackingCode = true;
include_once ("../login.php");
include_once ("../../../smart_form/include_form.php");

$arr['form'] = array ( 'id' => 'form_reg' , 'width' => '100%' , 'align' => 'center' , 'action' => "$relative_path" . 'inc/account_reg.php' , 'size' => '' , 'class' => '' , 'inline' => 'false' );

$arr['ajax'] = array ( 'success' => "
		if ( data == 'exist') {
			alert('$strUsernameExists');
		}
		else if ( data == 'ok') {
			$('#portal_content').load('$relative_path" . "sites/mask_successful_registration.php',{load_main:true})
			$('#modal_reg').modal('hide');
		}
		else 
			alert('Error');	
		", 'datatype' => 'html'  );

//$arr['sql'] = array ( 'query' => "SELECT * FROM $db_smart.tbl_user WHERE user_id = '{$_POST['update_id']}' " );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'three fields' );
$arr['field']['gender'] = array ('tab' => 'first', 'type'=>'select','label' => 'Anrede', 'array'=> array('f' => 'Frau', 'm'=> 'Herr'), 'class'=>'four wide',  'validate' => 'Bitte Anrede auswählen');
$arr['field']['firstname'] = array ( 'tab' => 'first' , 'label' => 'Vorname' , 'type' => 'input', validate=>'Bitte Vornamen angeben' , 'class'=>'wide seven'  );
$arr['field']['secondname'] = array ( 'tab' => 'first' , 'label' => 'Nachname' , 'type' => 'input', validate=>'Bitte Nachnamen angeben' , 'class'=>'wide six' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['company_1'] = array ( 'tab' => 'first' , 'label' => 'Firma' , 'type' => 'input' );

$arr['field']['street'] = array ( 'label' => 'Strasse' , 'type' => 'input' , 'placeholder' => 'Strasse', validate=>'Bitte Strasse eingeben');
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields' );
$arr['field']['zip'] = array ( 'tab' => 'first' , 'label' => 'Plz' , 'type' => 'input', 'class'=>'two wide' , validate=>'Bitte Plz angeben' );
$arr['field']['city'] = array ( 'tab' => 'first' , 'label' => 'Ort' , 'type' => 'input', 'class'=>'seven wide', validate=>'Bitte Ort angeben');
$arr['field']['country'] = array ( 'tab' => 'first' , 'label' => 'Land' , 'array'=>'country', 'type' => 'select', 'class'=>'seven wide', validate=>'Bitte Land angeben' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['mobil'] = array ( 'tab' => 'first' , 'label' => 'Telefon' , 'type' => 'input');

$arr['field']['email'] = array ( 'label' => 'Email' , 'type' => 'input' , 'placeholder' => 'email' ,  'validate' => 'email', label_right=>'(ist auch Username)' );
$arr['field']['password_new'] = array ( 'label' => 'Passwort' , 'type' => 'smart_password' );

$arr['field']['agb'] = array ('type'=>'checkbox', 'label' => "$strTncTitle <a href=# id=button_agb>$strButtonTnc</a>",  'validate' => $strTncTitleError);
$arr['field']['newsletter'] = array ('type'=>'checkbox', 'label' => "$strNewsletterSubcribe", 'value' => 1);

$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => "$strButtonAddNewAccount" , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('#modal_reg').modal('hide'); " );

$arr['field'][] = array ( text=>"<div class=error_msg align=center id=error_msg></div>");
$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];