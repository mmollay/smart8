<?php
include ('../t_config.php');
include (__DIR__ . '/../../../../smartform/include_form.php');

$arr['form'] = array('action' => "ajax/form_edit2.php", 'id' => 'form_edit', 'inline' => 'list');
//$arr['ajax'] = array('success' => "$('#modal_form').modal('hide').remove(); alert('test'); table_reload();", 'dataType' => "html");

include ('../form/f_' . $_POST['list_id'] . '.php');

$arr['hidden']['list_id'] = $_POST['list_id'];
// $arr['button']['submit'] = array('value' => "<i class='save icon'></i>Save", 'color' => 'blue');
//$arr['button']['close'] = array('value' => 'Close', 'color' => 'gray', 'js' => "$('.modal.ui').modal('hide'); ");
$output = call_form($arr);
echo $output['html'];
echo $output['js'];
echo $add_js;
