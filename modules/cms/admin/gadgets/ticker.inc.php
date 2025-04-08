<?php
$onLoad = "
		$('#ticker_text').enterKey(function () {
			$('#label_right_ticker_text').click();
		})
		
		$('#label_right_ticker_text').click( function() {
			var value =  $('#ticker_text').val();
			var layer_id = '{$_POST['update_id']}';
			if (value) { $.ajax({ url : 'admin/inc/add_tickertext.php', data : ({ value: value, layer_id : layer_id  }), global: false, type : 'POST', dataType : 'script' }) }
		})
		";

if (! $format)
	$format = '[[ticker]]';

$arr['field']['ticker_text'] = array ( 'tab' => 'first' , 'type' => 'input' , 'label' => 'Live-Ticker (Dieser Text erscheint Live auf der Webseite sobald abgesendet wurde)' ,  'label_right' => "<i class='send icon'></i> Absenden" ,  'label_right_class' => 'button' );
$arr['field']['hr'] = array ( 'tab' => 'first' , 'label' => "<hr>" );
// $arr['field']['align'] = array ( 'label' => "Ausrichtung" , "type" => "select" , 'array' => array ( 'left' => 'links' , 'center' => 'mittig' , 'right' => 'rechts' ) , 'value' => $align );
$arr['field']['format'] = array ( 'tab' => 'first' , 'type' => 'ckeditor_inline' , 'toolbar' =>'simple' , 'value' => $format );
$arr['field']['infotext'] = array ( 'tab' => 'first' , 'type' => 'text' , 'label' => 'Platzhalter: <b>[[ticker]]</b>' );
$arr['field'][''] = array ( 'tab' => 'first' , 'type' => 'button' , 'class_button' => 'mini blue' , 'value' => 'Text übernehmen' , 'onclick' => "save_value_element('$update_id','format',$('#format').html(),'marquee');" );
	