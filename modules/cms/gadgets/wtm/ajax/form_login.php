<?php
include_once ("../../../smart_form/include_form.php");

$arr['form'] = array ( 'id' => 'form_login' , 'width' => '600' , 'align' => 'center' , 'action' => 'gadgets/wtm/ajax/form_login2.php' , 'size' => '' , 'class' => '' , 'inline' => 'false' );

$arr['ajax'] = array ('success' => "
	if ( data == 'ok') {
		table_reload();
		userbar_reload();
		$('#modal_login').modal('hide');
	}
	else if ( data == 'error') {
		$('#error_msg').addClass('label ui red');
		$('#error_msg').html('User oder Passwort ist falsch!');
	}", 'datatype' => 'html', );

$arr['field']['client_username'] = array ( 'label'=>'Username', 'type' => 'input' , 'placeholder' => 'Email' , focus=>true, rules => array ( [ 'type' => 'email' , prompt => 'Email angeben' ] ) );
$arr['field']['client_password'] = array (  'label'=>'Passwort','type' => 'password' , 'placeholder' => 'Passwort' , rules => array ( [ type=> 'empty', prompt => 'Passwort eingeben' ] ) );

$arr['field'][] = array ( 'type' => 'content',  text=>"<div id=error_msg></div>");
$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => "Einloggen" , 'color' => 'blue' );
$output = call_form ( $arr );
echo "<div id=div_form_login>";
echo $output['html'];
echo $output['js'];
echo "</div>";