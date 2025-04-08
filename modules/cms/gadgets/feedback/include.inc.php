<?php
session_start();
if ($GLOBALS['set_ajax']) {
    $path = "../../";
}

$feedback_id = uniqid();
include_once ("$path" . "smart_form/include_form.php");

$success = "
		if (data == 'ok')
		{

		message = 'Versendung war erfolgreich!'; 
		$('#form_feedback$feedback_id').html('<div class=\"ui message\" align=center>'+message+'</div>');
		}
		else 
		{  alert(data);  }
";

if (! $send_button)
    $send_button = 'Nachricht senden';

$arr['form'] = array('id' => "form_feedback$feedback_id" ,'class' => $segment_size,'inline' => 'true','action' => 'gadgets/feedback/submit.php');
$arr['ajax'] = array('success' => "$success",'dataType' => 'html');

$arr['field'][] = array('tab' => 'first','type' => 'div','class' => 'two fields'); // 'label'=>'test'
$arr['field']['firstname'] = array('label' => 'Vorname','type' => 'input','placeholder' => 'Vorname','validate' => 'Bitte Namen angeben');
$arr['field']['secondname'] = array('label' => 'Nachname','type' => 'input','placeholder' => 'Nachname');
$arr['field'][] = array('tab' => 'first','type' => 'div_close');
$arr['field']['email'] = array('label' => 'Email','type' => 'input','placeholder' => '','validate' => 'email');
$arr['field']['telefon'] = array('label' => 'Telefon','type' => 'input','placeholder' => '');
$arr['field']['message'] = array('label' => 'Nachricht','type' => 'textarea','placeholder' => 'Bitte Nachricht angeben','validate' => true);
if ($recaptcha and $site_key)
    $arr['field'][] = array('type' => 'recaptcha','key' => "$site_key"); // siehe config.inc.php

$arr['hidden']['layer_id'] = $layer_id;
$arr['hidden']['from_id'] = $from_id;

$arr['buttons'] = array('align' => 'center');
$arr['button']['submit'] = array('value' => "<i class='icon mail outline'></i> $send_button",'color' => 'green');

$output_form = call_form($arr);

$output .= $output_form['html'];
$add_js2 .= $output_form['js'];
?>