<?php
include_once ("$path" . "smart_form/include_form.php");


$arr['form'] = array('id' => 'formplan','action' => 'gadgets/paneon_plan/result.php','class' => 'segment attached','width' => '','align' => 'center');
$arr['ajax'] = array('success' => "$('#paneon_show_data').html(data);",'dataType' => 'html');

// $arr['field'][] = array('type' => 'header','text' => '<b>GESUNDHEITSFAHRPLAN - Abfrage</b>','class' => 'green');

$arr['field'][] = array ('type'=>'div','class'=>'ui message');
$arr['field'][] = array('type' => 'header','text' => 'Tierangaben','class' => 'green');

include_once ('array.php');

$arr['field'][] = array ('type'=>'div_close');

foreach ($array_title as $key1 => $value) {

    $arr['field']['title' . $key1] = array('type' => 'content','text' => '<b>' . $array_title[$key1] . '</b>');
    foreach ($array_value[$key1] as $key => $value) {
        $arr['field'][$key] = array('type' => 'checkbox','label' => $value);
    }
    $arr['field']['comment' . $key1] = array('type' => 'textarea','label' => 'Bemerkungen');
}

$arr['field'][] = array ('type'=>'div','class'=>'ui message');
$arr['field'][] = array('type' => 'header','text' => 'Eigene Angaben','class' => 'green');
$arr['field'][] = array ('type' => 'div','class' => 'fields equal width' );
$arr['field']['user_name'] = array('type' => 'input','label'=>'Name', 'validate'=> true,'value'=>'Martin');
$arr['field']['user_email'] = array('type' => 'input','label'=>'Email', 'validate'=> 'email','value'=>'mm@ssi.at');
$arr['field']['user_tel'] = array('type' => 'input','label'=>'Telefon');
$arr['field'][] = array ('type'=>'div_close');
$arr['field']['user_comment'] = array('type' => 'textarea','label'=>'Ihre Nachricht');
$arr['field'][] = array ('type'=>'div_close');

$arr['field']['submit'] = array('type' => 'button','color' => 'red','value' => 'Persönliche Analyse durchführen','class' => 'submit','align' => 'center','icon' => 'hand point right');

$output_form = call_form($arr);


$output .= $output_form['html'];
$output .= "<div id ='paneon_show_data'></div>";
$output .= "<script type='text/javascript' src='gadgets/paneon_plan/query.js'></script>";
$add_js2 .= $output_form['js'];
