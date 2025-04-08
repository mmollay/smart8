<?php
/*
 * @author Martin Mollay
 * @last-changed 2105-05-22
 */
session_start ();
require_once '../config.php';
include ('../../smart_form/include_form.php');
date_default_timezone_set('Europe/London');

if ($_COOKIE['user_id']) $_SESSION['user_id'] = $_COOKIE['user_id']; 

if ($_SESSION['user_id']) {
	
	$_POST['update_id'] = $_SESSION['user_id'];
	
	$arr['sql'] = array ( 'query' => "SELECT * FROM ssi_company.user2company WHERE user_id = '{$_POST['update_id']}' " );
	$arr['tab'] = array ( 'tabs' => [ "first" => "Stammdaten" , "sec" => "Bilder" ] );
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'three fields' );
	$arr['field']['gender'] = array ('tab' => 'first', 'type'=>'select','label' => 'Anrede', 'array'=> array('female' => 'Frau', 'male'=> 'Herr'), 'class'=>'four wide',  'validate' => 'Bitte Anrede auswählen');
	$arr['field']['firstname'] = array ( 'tab' => 'first' , 'label' => 'Vorname' , 'type' => 'input', 'validate'=>'Bitte Vornamen angeben' , 'class'=>'wide seven'  );
	$arr['field']['secondname'] = array ( 'tab' => 'first' , 'label' => 'Nachname' , 'type' => 'input', 'validate'=>'Bitte Nachnamen angeben' , 'class'=>'wide six' );
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
	
	$arr['field']['street'] = array ( 'tab' => 'first' , 'label' => 'Strasse' ,'type' => 'input' );
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields' );
	$arr['field']['zip'] = array ( 'tab' => 'first' , 'label' => 'Plz' , 'type' => 'input', 'class'=>'two wide' , 'validate'=>'Bitte Plz angeben' );
	$arr['field']['city'] = array ( 'tab' => 'first' , 'label' => 'Ort' , 'type' => 'input', 'class'=>'seven wide', 'validate'=>'Bitte Ort angeben');
	$arr['field']['country'] = array ( 'tab' => 'first' , 'label' => 'Land' , 'array'=>'country', 'type' => 'select', 'class'=>'seven wide', 'validate'=>'Bitte Land angeben' );	
	$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
	$arr['field']['telefon'] = array ( 'tab' => 'first' , 'label' => 'Telefon' , 'type' => 'input');
	
//	$arr['field']['birthday'] = array ( 'tab' => 'first' , 'label' => 'Geburtstag' , 'type' => 'select', 'array' => range(date('Y')-10, date('Y')-100 ));
	$arr['field']['birthday'] = array ( 'tab' => 'first' , 'label' => 'Geburtstag' , 'type' => 'date');
	
	$arr['form'] = array ( 'action' => "gadgets/login_bar/form_edit2.php" , 'id' => 'form_edit' , 'size' => 'small' , 'inline' => 'list' );
	
	$arr['ajax'] = array (  'success' => "$('.ui.modal.login').modal('hide'); " ,  'dataType' => "html" );
	
	$arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'blue' );
	$arr['button']['close'] = array ( 'value' => 'Abbrechen' , 'color' => 'gray' ,  'js' => "$('.ui.modal.login').modal('hide'); " );
	$output = call_form ( $arr );
	echo $output['html'];
	echo $output['js'];
}

else{
	
	echo "<div class='message ui error'>User ist nicht definiert!</div>";
}