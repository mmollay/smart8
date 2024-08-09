<?php
session_start();
require_once ('../t_config.php');
include (__DIR__ . '/../../../smartform/include_form.php');

$arr['ajax'] = array('onLoad' => $onload, 'success' => "$('#modal_msg>.content').html(data); $('#modal_msg').modal('show');", 'dataType' => "html");

$arr['field'][] = array('id' => 'setTEXT', 'type' => 'textarea', 'label' => 'Templates', 'rows' => '15', 'validate' => true, 'value' => $liste);

$arr['header'] = array('text' => "<div class='content ui header small orange'><i class='icons'><i class='icon database'></i><i class='corner add icon'></i></i> Kontakte importieren</div>", 'segment_class' => 'message attached');
$arr['form'] = array('action' => "ajax/content_import2.php", 'id' => 'form_edit', 'width' => '800', 'class' => 'segment attached', 'size' => 'small');

$arr['button']['submit'] = array('value' => 'Kontakte importieren', 'icon' => 'send', 'color' => 'blue');
$arr_output = call_form($arr);

$content = $arr_output['html'];
$content .= "<div id=dialog_msg></div>";
$content .= $arr_output['js'];

$content .= "
<div class='ui small modal' id='modal_msg'>
	<div class='header'>Info</div>
	<div class='content'><p></p><p></p><p></p></div>
	<div class='actions'>
		<div class='ui cancel button' >Schließen</div>
	</div>
</div>";

echo $content;

?>