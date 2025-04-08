<?php
$set_TrackingCode = true;
include_once ("../login.php");
include_once ("../../../smart_form/include_form.php");

if (! $_SESSION['entry'])
	$_SESSION['entry'] = 'list_private';


$arr['form'] = array ( 'id' => 'form_login' , 'width' => '600' , 'align' => 'center' , 'action' => "$relative_path" . 'inc/login_user.php' , 'size' => '' , 'class' => '' , 'inline' => 'false' );

$arr['ajax'] = array ( 'success' => "
	if ( data == 'ok') {
		$('#portal_content').load('$relative_path" . "sites/portal.php',{load_main:true});
		$('#modal_login').modal('hide');
	}
	else if ( data == 'error') {
		$('#error_msg').html('User oder Passwort ist falsch!');
	}", 'datatype' => 'html'  );

$arr['field'][] = array ( 'id' => 'client_username' , 'label'=>'Username', 'type' => 'input' , 'placeholder' => 'Email' , focus=>true, rules => array ( [ 'type' => 'email' , prompt => 'Email angeben' ] ) );
$arr['field'][] = array ( 'id' => 'client_password' , 'label'=>'Passwort','type' => 'password' , 'placeholder' => 'Passwort' , rules => array ( [ type=> 'empty', prompt => 'Passwort eingeben' ] ) );
$arr['field'][] = array ( 'type' => 'content' , 'text' => '<div id=form_message></div>' );
$arr['field'][] = array ( 'type' => 'content',  text=>"<div class=error_msg align=center id=error_msg></div>");
$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => "Einloggen" , 'color' => 'blue' );
$output = call_form ( $arr );
echo "<div id=div_form_login>";
echo $output['html'];
echo $output['js'];
echo "</div>";
exit;