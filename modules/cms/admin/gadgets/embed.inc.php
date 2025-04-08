<?php
//$('.select > img') . tooltip ();
$onLoad .= "
		represention($('.select:checked').attr('id'));
		$('.select').bind ( 'keyup change focus', function () { represention(this.id) } );

		$('#aspect_ratio').change( function(){ 
				
			if ($('.select:checked').attr('id') == 'youtube') {
				if($(this).is(':checked')) {
     				$('#row_rel,#row_showinfo') . hide (); 
				$('.select:checked').attr('id')
				}
				else {
					$('#row_rel,#row_showinfo') . show();
				}
			}
		})		
			
		$('#code').change( function(){

			$.ajax( {
					url      : 'admin/ajax/form_gadget_autosave.php',
					data: {
  						'id' : 'code',
						'value' : $('#code').val(),
   						'player' : $('.select:checked').attr('id'),
						'update_id' : '$update_id'
				     },
					type     : 'POST',
					dataType : 'html',
					success  : function(data) { $('#code').val(data);	}
				}); 
		})	

		function represention(id) {
			if (id == 'youtube' || id == 'vimeo') { 
				$('#row_rel,#row_showinfo,#row_code,#row_autoplay') . show();				
				$('#row_link,#row_height') . hide ();
			} else if (id == 'iframe') { 
				$('#row_link,#row_height') . show (); 
				$('#row_rel,#row_showinfo,#row_autoplay,#row_code') . hide ();
				$('#height').keyup(function() { 
					if ($('#height').val()) { $('#aspect_ratio').attr('disabled', true); } 
					else { $('#aspect_ratio') . removeAttr ( 'disabled' ); }
				}) 
			}
			if ($('#height').val()) { call_check_height() }
		}
	
	function call_check_height() {
		if (this.checked) { $('#aspect_ratio').removeAttr('disabled'); } else { $('#aspect_ratio').attr('disabled', true); }  
	}
";

if (! $select)
	$select = 'youtube';
if (! $dimension)
	$dimension = '100';

if (! $icon && ! $_POST['update_id'])
	$icon = "circle arrow right";

$style = 'padding:5px 10px;';

$select_array = array ( 'youtube' => "<div class='label large basic ui' style='width:180px;'><i class='youtube  icon' title='Youtube' ></i> Youtube</div>" ,
		'vimeo' => "<div class='label large basic ui' style='width:180px;'><i class='vimeo icon' title='Vimeo'></i> Vimeo</div>" ,
		'iframe' => "<div class='label large basic ui' style='width:180px;'><i class=' browser  icon' title='Iframe'></i> Iframe</div>" );

// $dimension_array = array ( '100' => 'automatische Größe 100% (empfohlen)' , '90' => 'automatische Größe 90%' ,
// // 'size1'=>'560x315',
// 'size2' => '640x360' , 'size3' => '853x480' ,
// // 'size4'=>'1280x720',
// 'custorm' => 'Eigene Größe definieren' );

// $form->setField('gadget_id', "type=>hidden");

if ($gadget == 'pdf') {
	$arr['field']['link'] = array ( 'tab' => 'first' , 'label' => "PDF-URL" , 'type' => 'finder' , 'value' => $link );
	$arr['field']['title'] = array ( 'tab' => 'first' , 'label' => "Titel der PDF (optional)" , 'type' => 'input' , 'value' => $title );
} else {
	$arr['field']['select'] = array ( 'tab' => 'first' , "class" => 'select' , "label" => "Wähle" , "type" => "radio" , 'grouped' => true , 'array' => $select_array , 'value' => $select );
	$arr['field']['code'] = array ( 'tab' => 'first' , "label" => "URL oder Code hineinkopieren" , "type" => "input" , 'size' => '20' , 'value' => $code , 'placeholder' => 'https://www.youtube.com/watch?v=7JmprpRIsEY' , 'required' => TRUE , 'focus' => true );
	$arr['field']['link'] = array ( 'tab' => 'first' , "label" => "Website" , "type" => "input" , 'size' => '50' , 'value' => $link , 'required' => TRUE , 'required_text' => 'Bitte Adresse angeben' , 'placeholder' => 'http://www.google.at' );
	
	$arr['field']['placeholder'] = array ( 'tab' => 'first' , 'label' => "Platzhalder-Bild" , 'type' => 'finder' , 'value' => $placeholder );
	$arr['field']['icon'] = array ( 'tab' => 'first' , 'label' => "Icon" , 'type' => 'icon' , 'value' => $icon );
	$arr['field']['start_time'] = array ( 'tab' => 'first' , 'label' => "Startzeit" , 'type' => 'input' , 'value' => $start_time ,  'label_right' => 'sek' );
	$arr['field']['height'] = array ( 'tab' => 'first' , "type" => "input" , "label" => "Höhe" , 'value' => $height ,  'label_right' => 'px' );
	$arr['field']['aspect_ratio'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Seitenverhältnis 4:3' , 'value' => $aspect_ratio );
	$arr['field']['autoplay'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Video automatisch starten' , 'value' => $autoplay );
	$arr['field']['rel'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Weitere Videos nach Beendigung vorschlagen' , 'value' => $rel );
	$arr['field']['showinfo'] = array ( 'tab' => 'first' , 'type' => 'checkbox' , 'label' => 'Videotitel anzeigen' , 'value' => $showinfo );
}