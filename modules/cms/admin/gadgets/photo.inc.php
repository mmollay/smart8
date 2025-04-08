<?php
if (! $update_id)
	$resize = 1;

if ($set_modal)
	$set_target = 'open_modal';

$array_variations = array ( '' => 'Kantig' , 'rounded' => 'Abgerundet' , 'circular' => 'Kreisrund' );

$array_hover_effect = array ( 'image_opacity' => 'Deckkraft bei Mouseover' , 'image_opacity_reverse' => 'Deckkraft bei Default' );
$array_zoom_effect = array ( 'img-hover-zoom' => 'Zoomen' ,
		'img-hover-zoom img-hover-zoom--quick-zoom' => 'Schnelles Zoomen' ,
		'img-hover-zoom img-hover-zoom--point-zoom' => 'Pointiertes Zoomen' ,
		'img-hover-zoom img-hover-zoom--zoom-n-rotate' => 'Rotation' ,
		'img-hover-zoom img-hover-zoom--brightness' => 'Aufhellung' ,
		'img_parallax' => 'Parallaxeffect' );

$array_size = array_merge ( array ( 'full' => 'auf ganze Seite ausdehnen' ), $array_size );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'message ui' ); // fields two
$arr['field']['object_fit'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Darstellung - skaliert' , 'value' => $object_fit );
$arr['field']['cover_size'] = array ('class' => 'no_reload_element', 'tab' => 'first' , 'label' => 'Bild (Höhe)' , 'type' => 'slider' , 'min' => 50 , 'max' => 1000 , 'step' => 1 , 'unit' => 'px' , 'value' => $cover_size );
$arr['field']['cover_size_width'] = array ('class' => 'no_reload_element', 'tab' => 'first' , 'label' => 'Bild (Breite)' , 'type' => 'slider' , 'min' => 50 , 'max' => 1000 , 'step' => 1 , 'unit' => 'px' , 'value' => $cover_size_width );
$arr['field']['size'] = array ( 'tab' => 'first' , 'label' => 'Bildgröße' , 'type' => 'dropdown' , "array" => $array_size , 'value' => $size , 'value_default' => 'large' );

$arr['field']['variations'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , "array" => $array_variations , 'value' => $variations );
$arr['field']['zoom_effect'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'label' => 'Effekte' , 'value' => $zoom_effect , 'array' => $array_zoom_effect , 'clearable' => true );
$arr['field']['hover_effect'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'label' => 'Tranparents-Effekte' , 'value' => $hover_effect , 'array' => $array_hover_effect , 'clearable' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['photo_style'] = array ( 'tab' => 'first' , 'label' => 'Style (css)' , 'type' => 'input' , 'value' => $photo_style , 'placeholder' => 'max-height:150px;' );
$arr['field']['resize'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Vergrößerbar machen' , 'value' => $resize );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'fields inline' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => text , 'label' => "<div class='label ui'>ODER</div>" );
// $arr['field']['link'] = array ( 'tab' => 'first' , 'class' => 'inline field' , "label" => "Link" , "type" => "input" , 'size' => '50' , 'value' => $link , 'placeholder' => 'http://www.' );
// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
// $arr['field']['link'] = array ( 'tab' => 'first' , 'class' => 'inline field' , "label" => "Link" , "type" => "input" , 'size' => '50' , 'value' => $link , 'placeholder' => 'http://www.' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'message ui' ); // fields two
$arr['field']["image_title"] = array ( 'tab' => 'first' , 'label' => 'Titel' , 'type' => 'input' , 'value' => $image_title  );
$arr['field']["url"] = array ( 'tab' => 'first' , 'label' => 'Bild verlinken mit Seite' , 'type' => 'dropdown' , 'array' => $array_sites , 'value' => $url , 'search'=>true, 'clearable' => true );
$arr['field']["link"] = array ( 'tab' => 'first' , 'label' => 'ODER (externer Link)' , 'type' => 'input' , 'value' => $link , 'placeholder' => 'https://' );
$array_target = array ( "same_tab" => "gleichen Tab" , 'new_tab' => 'einem neuen Tab' , 'open_modal' => 'Fenster in Fenster' );
$arr['field']['set_target'] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'label' => 'Öffnen im' , 'value' => $set_target , 'array' => $array_target );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['explorer'] = array ( 'tab' => 'first' , "label" => "Bilderpfad (für externe Bilder)" , "type" => "input" , 'size' => '50' , 'value' => $explorer , 'placeholder' => 'https://www.','info'=>'Bitte einen absoluten Pfad angeben: Bsp.: https://www.ssi.at/bild.png, danach "Bild tauschen" Button drücken.' );
$arr['field'][''] = array ( 'tab' => 'first' , 'type' => 'button' , 'class_button' => 'mini blue' , 'value' => 'Bild tauschen' , 'onclick' => "" );
// $arr['hidden']['explorer'] = $explorer;

$onLoad .= "	 $('#checkbox_object_fit').checkbox( {
		onUnchecked : function() { $('#row_cover_size,#row_cover_size_width').hide();  $('#row_size').show(); },
		onChecked : function() { $('#row_cover_size,#row_cover_size_width').show(); $('#row_size').hide(); }
});

if ($('#object_fit').is(':checked') == true ) { $('#row_cover_size,#row_cover_size_width').show(); $('#row_size').hide(); } else { $('#row_cover_size,#row_cover_size_width').hide();  $('#row_size').show(); }
";


// $onLoad .= "
// 		represention($('.setting:checked').attr('id'));
// 		$('.setting').bind('keyup change',function() { represention(this.id) });
		
// 		function represention(id){
// 			$('#row_camp_key,#row_button_inline,.buttons_url').hide();
// 			if (id =='sign_in' ){ $('#row_camp_key,#row_button_inline').show();  }
// 			else if (id =='to_complete' ) { $('.buttons_url').show(); }
// 		}
// 		";
 
