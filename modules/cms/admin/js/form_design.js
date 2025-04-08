
//Automatisches Speichern der Arrayparameter
function save_value(key,value) {
	
	if( xhr != null ) {
        xhr.abort();
        xhr = null;
        
	}

	xhr = $.ajax( {
		url      : 'admin/ajax/form_design_autosave.php',
		data     : ({'id':key,'value':value}),
		type     : 'POST',
		dataType : 'html',
		success  : function(data) { $('#set_style').html(data); }
	});
} 


function call_form_design() {
	
	$('.form_design:not(.no_auto_save)').bind('focus change', function() {
		//console.log('this actually works');
		save_value( this.id,$('#'+this.id).val()); 
		if (this.id == 'body_font_google' || this.id == 'body_font_googlefamily' ) {
			var str = $('#'+this.id).val();
			str = str.replace(/ /g, '+');
			$('#css_google').attr('href',"https://fonts.googleapis.com/css?family="+str);
			$('.smart_content_container,h1,h2,h3,h4').css('font-family',$('#'+this.id).val()+", sans-serif");
		}
	});
	
//	
//	$('.ui-input,.ui-color,.ui-dropdown').change(function(){ 
//		save_value( this.id,$('#'+this.id).val()); 
//		if (this.id == 'body_font_google' ) {
//			var str = $('#'+this.id).val();
//			str = str.replace(/ /g, '+');
//			$('#css_google').attr('href',"https://fonts.googleapis.com/css?family="+str);
//			$('.smart_content_container,h1,h2,h3,h4').css('font-family',$('#'+this.id).val()+", sans-serif");
//		}
//	});
	
	//Save silder data
	$('.ui-slider-value.form_design').bind("DOMSubtreeModified",function(){ save_value( this.id,$('#'+this.id).html()); });
	
	
	//Change SLIEDER
	//$('.ui-slider').on("slide",function(event, ui) {  save_value(this.id,ui.value);  });
	
	//Change CHECKBOX
	$('.ui-checkbox').change(function() { 
		if ($('#'+this.id+':checked').val()){ save_value(this.id,1); }
		else { save_value(this.id,0); }
	});
		
	//Change STYLE (textarea)	
	$('#style').keyz({
		'enter tab shift': function(ctl,sft,alt,event) {
		$('#edit_layout').html($('#style').val());
		save_value('style',$('#style').val());
		}
	});
	
	
	//Bei 100% Breite wird Bodystyle ausgblendet
	if ($('#checkbox_content_width_100').checkbox('is checked')) { $('.show_bodystyle').hide(); } //hide border-design becaus of 100% width
	$('#checkbox_content_width_100').checkbox( {
		onUnchecked : function() { $('.show_bodystyle').show();  }, //if($('#body_border_on').is(":checked") == false )  $('.show_body_border').hide(); 
		onChecked : function() { $('.show_bodystyle').hide() }
	});
	
	//href=\"https://fonts.googleapis.com/css?family=$body_font_google[0]\"

	//Element Breite manuel einstellen ausblenden
	if ( $('#checkbox_element_fullsize_all').checkbox('is checked')) { $('#row_content_max_width').hide(); } 
	$('#checkbox_element_fullsize_all').checkbox ({
		onUnchecked : function() { $('#row_content_max_width').show(); },
		onChecked : function() { $('#row_content_max_width').hide();  }
	});
	
	
	//Rahmendesign ausblenden
	if ($('#checkbox_body_border_on').checkbox('is checked'))  { $('.show_body_border').show(); } else { $('.show_body_border').hide(); }
	$('#checkbox_body_border_on').checkbox ({
		onUnchecked : function() { $('.show_body_border').hide() },
		onChecked : function() { $('.show_body_border').show();  }
	});
	
	
	//Bread-crumb
	//if ($('#bread_visible').is(":checked") == false) {  $('.content_breadcrumb').hide(); } else { $('.content_breadcrumb').show(); }
	if ($('#checkbox_bread_visible').checkbox('is unchecked')) { $('.content_breadcrumb').hide(); } 
	$('#checkbox_bread_visible').checkbox ({
		onChecked : function() { $('.content_breadcrumb').show(); },
		onUnchecked : function() { $('.content_breadcrumb').hide() }
	});
		
	//Kopfzeile
	if ($('#checkbox_header_none').checkbox('is checked'))  { $('.content_header').hide(); }
	$('#checkbox_header_none').checkbox ({
		onUnchecked : function() { $('.content_header').show() },
		onChecked : function() { $('.content_header').hide(); }
	});
	
	
//	if ($('#menu_hidden:checked').val()){ $('.hide_menu').hide(); } else { $('.hide_menu').show(); }
//	
//	//Editierfelder für Fusszeile2 einblenden
//	if ($('#footer2_show:checked').val()){ $('.show_footer2').show(); } else { $('.show_footer2').hide(); }
//	
	//move_background('smart_content_header','header_backgroundposition');
	//move_background('smart_content_footer','footer_backgroundposition');

	//$('.smart_content_header').backgroundDraggable('disable');
}

function call_border_radius(value_size,value_radius) {
	
	if (value_size == 0 || value_size)
		var wert  = value_size;
	else
		var wert = $( "#body_border_size" ).slider( "option", "value" );
	
	if (value_radius == 0  || value_radius ) 
		var wert1 = value_radius;
	else
		var wert1 = $( "#body_radius" ).slider( "option", "value" );
	
	
	var wert2 = wert + wert1;
	
	//Wenn Kopf nicht angezeigt wird, dann aendert sich der Border vom Content_BODY
	if ($('#header_none:checked').val()){ 
		$('.smart_content_body').css ({ 'border-radius': wert1+'px '+ wert1+'px 0px 0px' }); 
	}
	else {
		$('.smart_content_footer').css ({ 'border-radius': '0px 0px '+wert1+'px '+ wert1+'px' }); 
	}
	
	if (wert1 != 0) {
		$('.smart_content').css ({ 'border-width': wert+'px', 'border-radius':  wert2+'px' });
		$('.smart_content_header').css ({ 'border-radius': wert1+'px '+ wert1+'px 0px 0px' });
	} else {
		$('.smart_content').css ({ 'border-radius':  '0px' });
		$('.smart_content_header').css ({ 'border-radius': '0px 0px 0px 0px' });
	}		

	if (wert === 0) {
		$('.smart_content').css( {'border' :'thin solid transparent'});
	}else {
		$('.smart_content').css ({ 'border': wert+'px solid '+$('#body_border_color').val() });		
	}
}

function move_background(id,css) {
	//Speichert den Hintergrund in ihrer Postion
	$('.'+id).on({
			mouseenter:function() { $(this).css('cursor','move'); }, 
			mouseleave:function() { }
	});
	
	var backgroundPos = $('.'+id).css('backgroundPosition').split(" ");
	//now contains an array like ["0%", "50px"]

	var xPos = parseFloat(backgroundPos[0], 10);  
	var yPos = parseFloat(backgroundPos[1], 10);  

	 var $bg = $('.'+id),
     origin = {x: xPos, y: yPos},
     start = {x: xPos, y: yPos},
     movecontinue = false;
	
	function move (e){
        var moveby = {
            x: origin.x - e.clientX, 
            y: origin.y - e.clientY
        };
        
        if (movecontinue === true) {
            start.x = start.x - moveby.x;
            start.y = start.y - moveby.y;
            
            $(this).css('background-position', start.x + 'px ' + start.y + 'px');
        }
        
        origin.x = e.clientX;
        origin.y = e.clientY;
        
        e.stopPropagation();
        return false;
    }
    
    function handle (e){
        movecontinue = false;
        $bg.unbind('mousemove', move);
        
        if (e.type == 'mousedown') {
            origin.x = e.clientX;
            origin.y = e.clientY;
            movecontinue = true;
            $bg.bind('mousemove', move);
        } else {
            $(document.body).focus();

            backgroundPosition = $('.'+id).css('background-position');
            if (Cookies.get("backgroundPosition") != backgroundPosition ) {
            	save_value(css,backgroundPosition)
            }
            Cookies.set("backgroundPosition", backgroundPosition, { path: '' });
        }
        
        e.stopPropagation();
        return false;
    }
    
    function reset (){
        start = {x: 0, y: 0};
        $(this).css('backgroundPosition', '0 0');
    }
    
    $bg.bind('mousedown mouseup mouseleave', handle);
    $bg.bind('dblclick', reset);   
}
