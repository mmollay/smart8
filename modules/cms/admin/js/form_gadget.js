function save_value_element2 (update_id,id,value,gadget) {
	
	if( xhr2 != null ) {
	     xhr2.abort();
	     xhr2 = null;
	}
	
	xhr2 = $.ajax( {
		url : 'admin/ajax/form_gadget_autosave.php',
		data : {
			'update_id' : update_id, 
			'id' : id,
			'value' : value,
			'gadget' : gadget
	     },
		type     : 'POST',
		dataType : 'html',
		//async: false,
		beforeSend : function() { 
			if ($('#'+id).hasClass("no_reload_element") == false) {
				//$('#sort_'+update_id).prepend('<div class="ui active inverted dimmer"><div class="ui text loader">Element wird geladen</div>');
			}
		},
		success  : function(data) {
			if (id == 'rowHeight') {
				$('#flex-images'+update_id).flexImages({rowHeight: value });
			}
			
			else if (id == 'parallax_color' || id == 'parallax_color2') {
				
				$('#sort_'+update_id).css('background-color',value);
				
				if ($('#parallax_color').val() && $('#parallax_color2').val()) 
					$('#sort_'+update_id).css('background-image','url('+$('#parallax_image').val()+'), linear-gradient('+$('#parallax_color').val()+', '+$('#parallax_color2').val()+')');
				
			}
			else if (id == 'parallax_height') { 
				if (value == 0) {
					$('#sort_div_'+update_id).css('max-height','none');
					$('#sort_div_'+update_id).css('overflow','visible');
				}
				else {
					$('#sort_div_'+update_id).css('max-height',value+'px');
					$('#sort_div_'+update_id).css('overflow','auto');
				}
			}
			else if (id == 'parallax_padding') { 
				if (!value) value='0';
				$('#sort_div_'+update_id).css('padding-top',value+'px');
				$('#sort_div_'+update_id).css('padding-bottom',value+'px');
			}
			else if (id == 'parallax_padding_lr') { 
				if (!value) value='0';
				$('#sort_div_'+update_id).css('padding-left',value+'px');
				$('#sort_div_'+update_id).css('padding-right',value+'px');
	  	 	}
			else if( id == 'element_width') {
				$('#sort_div_'+update_id).css('width',value+'%');
			}
			else if (id == 'element_margin') { 
				if (!value) value='0';
				$('#sort_'+update_id).css('margin-top',value+'px');
				$('#sort_'+update_id).css('margin-bottom',value+'px');
				//$('#sort_'+update_id).css("cssText", "margin-top:"+value+"px !important; margin-bottom:"+value+"px !important;");
	  	 	}
			else if (id == 'element_margin_lr') { 
				if (!value) value='0';
				$('#sort_'+update_id).css('margin-left',value+'px');
				$('#sort_'+update_id).css('margin-right',value+'px');
	  	 	}
			else if (id == 'cover_size') {
				$('.image'+update_id).css('height',value+'px');
				//$('.image'+update_id).css('width',value+'px');
			}
			else if (id == 'cover_size_width') {
				//$('.image'+update_id).css('height',value+'px');
				$('.image'+update_id).css('width',value+'px');
			}
			else { //if ($('#'+id).hasClass("no_reload_element") == false)
				$('#sort_'+update_id).replaceWith(data);
				SetNewTextfield ();

				//Ckeditor wird neu geladen, nach Veränderung				
				if (gadget == 'textfield') {
					CKEDITOR.instances['layer_text'+update_id].destroy();
					save_content_id('layer_text'+update_id);
				}
			}
			
			$('#save_icon_gadget').stop(true,true).show().fadeOut(2000);
			
			//var value = 'sort_'+update_id;
     		//$('#' + value + '.textfield_div').css( {'box-shadow': '0px 0px 2px 2px red'});
     		//$('#' + value + '.splitter_div').css( {'box-shadow': '0px 0px 2px 2px red'});		
		}
	});
}

//Automatisches speichern ohne Reload für Menüdesign
function call_form_design_menu() {
	
	//Change INPUT and SELECT
	$('.ui-input,.ui-dropdown').keyz({
		'enter tab shift': function(ctl,sft,alt,event) { save_value_menu( this.id,$('#'+this.id).val()); }
	});
	
	$('.ui-input,.ui-color').change(function(){ save_value_menu( this.id,$('#'+this.id).val()); });
	
	
	//Change SLIEDER
	//$('.ui-slider').on("slide",function(event, ui) {  save_value_menu(this.id,ui.value);  });
	$('.ui-slider-div').bind("DOMSubtreeModified",function(){ save_value_menu( this.id,$('#'+this.id).html()); });
	
	//Change CHECKBOX
	$('.ui-checkbox').change(function() { 
		if ($('#'+this.id+':checked').val()){ save_value_menu(this.id,1); }
		else { save_value_menu(this.id,0); }
	});
}


//Automatisches Speichern der Arrayparameter MENU (v7.0)
function save_value_menu(key,value) {
	if( xhr != null ) {
        xhr.abort();
        xhr = null;
	}
	
	var edit_layer_id = $('input#edit_layer_id').val();
	
	xhr = $.ajax( {
		url      : 'admin/ajax/form_design_menu_autosave.php',
		data     : ({'layer_id':edit_layer_id,'id':key,'value':value}),
		type     : 'POST',
		dataType : 'html',
		success  : function(data) { $('#set_style_menu').html(data);  }
	});
} 

/***
 * Ladet das Element neu
 * reload_element (id)
 */

//Funktions nach speichern der Gallery-options
function form(){
	//Close Dialog
	content = $('#ProzessBar').val();	
	
	SetNewTextfield();
	//Messageausgabe
	$('#ProzessBarBox').message({ status:'info', title: 'Feld wurde angelegt' });	
}

//Funktion nach speichern der Gallery-optionen
function form_update(update_id){
	content = $('#ProzessBar').val();	
	// Neuen Layer anlegen
	$('#layer_text'+update_id).html(content);
	$('#ProzessBarBox').message({ status:'info', title: 'Feld wurde &uuml;berarbeitet' });
}


//Ermöglicht, dass alle Felder in der angegebenen Form automatisch gespeichert werden
function load_autosave(update_id,gadget) {	
	
	fu_segment_or_message($('#segment:checked').attr('id'));
	$('#segment').bind('change', function() {  fu_segment_or_message($('#segment:checked').attr('id')); });
	
	$('#segment_or_message').bind('keyup change focus',function() { fu_segment($('#segment_or_message').val()) });
	
	fu_show_label($('#show_label:checked').attr('id'));
	$('#show_label').bind('change', function() { fu_show_label($('#show_label:checked').attr('id')); });
	
	//Wenn SLIDER verwendet wird muss der jeweilige Slide-ID für den autosave eingetragen werden
	//$('#autoload_sec,#owl_item,#owl_height,#correctness_percent').on("slide",function(event, ui) { var wert = ui.value; save_value_element(update_id,this.id,wert) });
	
	$('.ui-slider-value.form_element').bind("DOMSubtreeModified",function(){  if($('#'+this.id).html()) save_value_element(update_id,this.id,$('#'+this.id).html()); });
	
	//$('#rowHeight,#autoload_sec,#owl_item,#owl_height,#correctness_percent,#parallax_height,#element_width,#element_margin,#element_margin_lr,#parallax_padding,#parallax_padding_lr,#cover_size,#cover_size_width').bind("DOMSubtreeModified",function(){  if($('#'+this.id).html()) save_value_element(update_id,this.id,$('#'+this.id).html()); });

	//$('#autoload_sec_hidden,#owl_item_hidden,#owl_height_hidden,#correctness_percent_hidden').bind('change', function() { save_value_element(update_id,this.id,$('#'+this.id).html()); });
	
	//Editierfelder für Kopfzeile  ausblenden
	fu_show_label_parallax($('#parallax_show:checked').attr('id'));
	$('#parallax_show').bind('change', function() {  fu_show_label_parallax($('#parallax_show:checked').attr('id')) });
	
	$('.textfield_div,.splitter_div').removeClass('hover_box_green');
	var value = 'sort_'+$('#update_id').val();
	$('#' + value + '.textfield_div').addClass('hover_box_green');
	$('#' + value + '.splitter_div').addClass('hover_box_green');	

	//$('#parallax_height').on("slide",function(event, ui) { var wert = ui.value; $('#sort_'+update_id).css ({ 'height': wert+'px' }); save_value_element(update_id,'parallax_height',wert) });	
	
	//	$('#parallax_filter_color').bind('focus change',function()   { 
	//		save_value_element(update_id,'parallax_filter_color',$(this).val());
	//		$('#'+update_id+'.parallax_filter').css ({ 'background-color': $(this).val()   }); 
	//	});
	

	//$('#parallax_color').bind('focus change',function() { $('#sort_'+update_id).css ({ 'background-color': $(this).val() }); });
	//$('#parallax_color2').bind('focus change',function() { $('#sort_'+update_id).css ({ 'background-color': $(this).val() }); });
	
	$('.form_element:not(.no_auto_save)').bind('focus change', function() {
	
		id = this.id;
		if (id) {
			value = $('#'+id).val();
			//fu_save_element(update_id,key,value,gadget);
			save_value_element(update_id,id,value,gadget)
		}
	});
	
}

function fu_segment_or_message(id) {
	
	if (id != 'segment') $('.show_segment').hide();
	else fu_segment($('#segment_or_message').val());
}

function fu_segment(id){	
	$('.show_segment').show();
	if (id =='segment')      { $('#row_segment_color,#row_segment_or_message,#row_segment_type,#row_segment_grade,#row_segment_inverted,#row_segment_disabled').show(); }
	else if (id =='message') { $('#row_segment_grade,#row_segment_inverted,#row_segment_type').hide(); $('#row_segment_or_message,#row_segment_color').show(); }
}

function fu_show_label(id) {
	if (id != 'show_label') $('.div_show_label').hide();
	else $('.div_show_label').show();
}

function fu_show_label_parallax(id) {
	if (id != 'parallax_show') $('.show_parallax').hide();
	else $('.show_parallax').show();
}


function fu_save_element(id,key,value,gadget){	
	 $.ajax({
	 	url: 'inc/autosave_element.php',	
	     type: "POST",
	     data: {
	         value : value,
	         id : id,
	         key : key,
	         gadget : gadget
	     },
	     success: function(data){ 
	     	if (data == 'ok') { 
	     		$('#short_info_box').fadeIn().delay('2000').fadeOut( "slow" ); 
	     	} 
	     	else { alert('Fehler beim speichern aufgetreten'); }
	     },
	     dataType: "html"
	 });
}

//Alle Menues werden neu geladen
//reload_gadget_class('menu_field');
function reload_gadget_class($class) {
	
	if ($class=='menu_field') 
		gadget = 'menu';
	
		$('.'+$class).each(function() {
			save_value_element2 (this.id,'','',gadget);
		});
		
		//save_value_element2 (this.id,'','',gadget);

}

//Automatisches Speichern der Array-parameter
function save_value_element(update_id,id,value,gadget) {
	
	//Übergabe der Checkboxparameter wenn 1 oder 0 ist
	if ( $('#'+id).attr("type") == 'checkbox' && (value == '1' || value == '0')) {
		if ($('#'+id).is(":checked")) value = 1;
		else value = 0;
	}

	//Wenn radio ist, dann wird der name ausgelesen, da bei smart_form id = den value beinhaltet!
	if ($('#'+id).is(":radio")) {
		id = $('#'+id).attr('name');
	}
	
	//Legt gegebenfalls ein neues Gästebuch an
	if (gadget == 'guestbook') {
		$.ajax( {
			url : 'admin/ajax/form_gadget_change_guestbook.php',
			data: { 
				'guestbook_id' : $('#guestbook_id').val(),
				'guestbook_name' : $('#guestbook_name').val()
			},
			type     : 'POST',
			dataType : 'html',
			success  : function(value) { save_value_element2 (update_id,'guestbook_id',value, gadget); }
		});
	}
	
	if (id == 'code') {
		//Ruft YoutubeCode ab und übergibt ihn
		$.ajax( {
			url : 'admin/ajax/form_gadget_changecode.php',
			data: { 
				'value' : value,
				'player' : $('.select:checked').attr('id')
			},
			type     : 'POST',
			dataType : 'html',
			success  : function(value) { 
				$('#code').val(value); 
				save_value_element2 (update_id,id,value,gadget);			
			}
		});
	}
	else if (id == 'text') {
		
		save_value_element2 (update_id,id,$('#'+id).html(),gadget);
	}
	else {
		
		save_value_element2 (update_id,id,value,gadget);
	}
} 

//Layer wird neu geladen
function reload_element (id) {
	if( xhr != null ) {
	     xhr.abort();
	     xhr = null;
	}	

	xhr = $.ajax( {
		url      : 'admin/ajax/layer_new_inc.php',
		data: {
			'layer_id' : id, 
	     },
		type     : 'POST',
		dataType : 'html',
		beforeSend : function() { 
			$('#sort_'+id).prepend('<div class="ui active inverted dimmer"><div class="ui text loader">...Element wird geladen</div>');
		},
		success  : function(data) {
			$('#sort_'+id).replaceWith(data);
			SetNewTextfield ();
		}
	});
}

//Holt von Amazon Parameter und erzeugt daraus Felder 
function preview_amazon_article(layer_id,id,generate = false) {
	if (!id) { 
		$('#ProzessBarBox').message({ status:'info', title: 'Bitte Produkt-ID eingeben!' }); 
		$('#link').focus(); 
		return;
	}
	
	if ( generate == 0 ) {
		$(".amazon-article").html(`
		<div class="ui basic segment"><br><br>
		<div class="ui active inverted dimmer"><div class="ui text loader">Amazon-Artikel wird erzeugt...</div></div><p></p></div>
		<iframe id='iframe_amazon' src="" style='display: none'></iframe>`
		);
		$('.ui.modal.amazon').modal({ allowMultiple: true, observeChanges : true });
		$('.ui.modal.amazon').modal('show');
	} else {
		$(".amazon-article").html(`
		<i class="notched circle loading icon"></i>
		  <div class="content">
		    <div class='header'>Dein Amazon-Artikel wird erzeugt</div>
		    <p>Bitte gedulde dich ein wenig...</p>
		  </div>
		<iframe id='iframe_amazon' src="" style='display: none'></iframe>`
		);
	}
	
	$('#amanzon_title,#amanzon_text,#amanzon_price').html('');
	$('#amanzon_pic').attr('src','');
	$('#iframe_amazon').attr('src','admin/ajax/amazon/iframe.php?product_id='+id+'&layer_id='+layer_id+'&generate='+generate);
}