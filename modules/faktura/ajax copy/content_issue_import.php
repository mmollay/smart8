<?php
require_once ('../config.inc.php');
include ('../../ssi_smart/smart_form/include_form.php');

$label_list .= "<div id='sortable_label'>";
foreach ( $array_issue_import as $key => $text ) {
	$label_list .= "<div class='label basic ui' style='cursor:move' id='$key' >$text</div>";
}
$label_list .= "</div><div id='sortable-10'></div>";

$arr ['ajax'] = array ('onLoad' => $onload,'success' => "$('#modal_msg>.content').html(data); $('#modal_msg').modal('show');",'dataType' => "html" );

$arr ['field'] [] = array ('type' => 'div','class' => 'fields' );
$arr ['field'] [] = array ('id' => 'setDelimiter','type' => 'input','label' => 'Trennzeichen','label_right' => '(Bsp.: tab=Tablulator, #, ...)','class' => 'six wide','value' => 'tab' );
$arr ['field'] [] = array ('type' => 'div_close' );

//$arr ['field'] ['info_list'] = array ('type' => 'content','class' => 'ui message ','text' => "<b>Importreihenfolge der Felder</b>(<i class='crosshairs icon'></i>Durch verschieben definierbar)<br><br>$label_list" );
$arr ['field'] ['info_list'] = array ('type' => 'content','class' => 'ui message ','text' => "<b>Importreihenfolge der Felder</b>$label_list" );
$arr ['field'] [] = array ('id' => 'setTEXT','type' => 'textarea','label' => 'Templates','rows' => '15','validate' => true,'value' => $liste );
 

$arr ['header'] = array ('text' => "<div class='content ui header small orange'><i class='icons'><i class='icon database'></i><i class='corner add icon'></i></i> Ausgaben importieren</div>",'segment_class' => 'message attached' );
$arr ['form'] = array ('action' => "ajax/content_issue_import2.php",'id' => 'form_edit','width' => '800','class' => 'segment attached','size' => 'small' );

$arr ['button'] ['submit'] = array ('value' => 'Ausgaben importieren','icon' => 'send','color' => 'blue' );
$arr_output = call_form ( $arr );

$content = $arr_output ['html'];
$content .= "<div id=dialog_msg></div>";
$content .= $arr_output ['js'];

$content .= "
<div class='ui small modal' id='modal_msg'>
	<div class='header'>Info</div>
	<div class='content'><p></p><p></p><p></p></div>
	<div class='actions'>
		<div class='ui cancel button' >Schließen</div>
	</div>
</div>";

$content .= "<script type=\"text/javascript\" src=\"js/form_issue_import.js\"></script>";

echo $content;

?>