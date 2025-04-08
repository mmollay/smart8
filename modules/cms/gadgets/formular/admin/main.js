//http://www.jqueryscript.net/text/Simple-In-line-Editing-Plugin-For-jQuery-Quick-Edit.html

function load_edit_formular(id) {
	//$('#form_button_feedback'+id).fadeOut();
	
	$('#sticky'+id).sticky({ pushing: true, offset : 43, context: '#context_form'+id });

//	$("#smart_content_body").hover(function(){
//		if (Cookies.get("edit_modus") == 'on') { $('#form_button_feedback'+id).fadeIn("slow"); }
//	},
//	function(){
//		if (Cookies.get("edit_modus") == 'on') {  $('#form_button_feedback'+id).fadeOut(); };
//	});

	call_inline_edit();
	check_empty_form();
	/***************************************************************************
	 * Module in die Seite hineinziehen
	 **************************************************************************/
	$('.new_form_field').draggable({
		connectToSortable: ".sortable_formular",
		 start: function( event, ui ) {
			$('.new_form_field').removeClass('move_draggable_form');
             $(this).addClass('move_draggable_form');
             $('#empty_field').hide();
             $('.form-field').css({'padding':'10px 0px'});
            
        },
        stop: function (){ 
        		//check_empty_form ();
        		$('.form-field').css({'padding':'0px 0px'});
        },
		helper: "clone",
		opacity: 0.7,
		revert: "invalid",
		cursor: "move"
	});
	
	$( ".sortable_formular" ).droppable({
		accept: ".new_form_field",
		tolerance: 'pointer',
	});
	
	/***************************************************************************
	 * Layer sortable
	 **************************************************************************/
	$( ".sortable_formular" ).sortable({
		//containment :'#garten',
		//containment : 'window',
		connectWith: ".sortable_formular",
		handle     : '.move',
		//revert: true,
		//distance: '100',
		tolerance: "pointer",
		//zIndex     : '400',
		placeholder: "ui message green",
		//revert: '0.5',
		tolerance: 'pointer',
		cursor     :'move', // Curvor bei Verschiebung mit "move" Symbol
		start : function(event,ui) { 
			//$('.tooltip,.edit_tooltip').tooltipster('destroy');		
			$( ".sortable_formular" ).css({'border':'1px dashed green'});
		},
		update     : function(event, ui) { 
			$( ".sortable_formular" ).css({'border':'0px'});
			
			ui.item.addClass("aktuell");
			 $('.aktuell').attr('style','');
			 $('.aktuell').removeClass('label aktuell');
			
			if (ui.item.hasClass("new_form_field")) {
	           
	        	var type = $(".move_draggable_form").attr('id');
	        	// This is a new item
	        	ui.item.removeClass("new_form_field");
	        
	        //Speichert neues Textfeld
		    	$.ajax( {
		    			url :"gadgets/formular/admin/field_new.php",
		    			global :false,
		    			async :false, //muss bestehen bleiben damit die Parameter nach success übergeben werden (new_textfield)
		    			data :( {  type :type, layer_id: $('#'+this.id).attr('id') }),
		    			type :"POST",
		    			dataType :"html",
		    			success  : function(data) { 
		    				ui.item.replaceWith(data);
		    				call_inline_edit();
		    			 	$( '.cktext' ).ckeditor();
		    			 	//CKEDITOR.replaceClass='cktext';
		    			}
		    	});	
		    	
		    	$('.new_form_field').removeClass('move_draggable_form');   
			}
			
			//Sortierung auslesen und in der Datenbank speichern
			serial = $('#'+this.id).sortable('serialize');
			
			id_position = this.id;
			$.ajax({ url:'gadgets/formular/admin/field_sort_save.php?id_position='+id_position , data : serial, type: "post", dataType: 'script' });
		}
	});
	
	$( '.cktext' ).ckeditor();
	
}



function call_inline_edit() {
	
	$('.sortable_formular>.form-field').hover(function () {
		var id = this.id;
		
		if (Cookies.get("edit_modus") == 'on') {
			if(!$('body').hasClass('ckeditor')) { 
				$(this).css({"background-color":"#EEE","outline":"1px dashed green"});
				$(this).prepend(`
				<div  id='div`+id+`' style='position:absolute; right:0px; z-index:100000'>
				<div class='ui mini buttons row_field' style='position:relative; top:0px; background-color:white;'>
				<a class='ui icon small button' data-tooltip='Feld bearbeiten' onclick=edit_field('`+id+`')><i class='icon edit'></i></a>
				<a class='ui icon small button move' data-tooltip='Feld verschieben'><i class='icon move'></i></a>
				<a onclick=rm_field('`+id+`') class='ui icon small button' data-tooltip='Feld löschen'><i class='icon trash'></i></a>
				</div>
				</div>`);
			}
		}
	},function () {
		$(this).css({"background-color":"","outline":""});
	    $('.buttons.row_field').remove();
	});
	
	
	$('.label.formular').on('click', function () {
		if (Cookies.get("edit_modus") != 'on') {return}
		id = this.id;
		var edit = $(this).quickEdit('create', {
        	id : id,
			blur: false,
            checkold: true,
            space: false,
            showbtn: false,   
            tmpl: '<span qe="scope"><span><input type="text" qe="input"/ style="min-width:400px">',
            submit: function (dom, newValue,id) {
                $.ajax( {
	    			url :"gadgets/formular/admin/save_label.php",
	    			global :false,
	    			async :false, //muss bestehen bleiben damit die Parameter nach success übergeben werden (new_textfield)
	    			data :( {  id : id, value : newValue }),
	    			type :"POST",
	    			dataType :"html",
	    			success  : function(data) { dom.text(newValue); }
                });	
            }
        });
        $(this).after(edit);
        $('input', edit)[0].select();
    });

}

//Entfernt ein Eingabefeld
function rm_field(id) {
	 $.ajax( {
			url :"gadgets/formular/admin/rm_field.php",
			global :false,
			async :false, //muss bestehen bleiben damit die Parameter nach success übergeben werden (new_textfield)
			data :( {  id : id }),
			type :"POST",
			dataType :"html",
			success  : function(data) { if (data == 'ok') { $('#'+id).remove(); check_empty_form();} }
     });	
}


//Prüft ob das Fomular leer ist 
function  check_empty_form () {
	
	if (!$('.form-field').length > 0) $('.sortable_formular').html("<div id='empty_field' align=center style='border:1px dashed silver'><br>Gewünschte Felder hineinziehen<br><br></div><br>");
}

function edit_field(id) {

	 $.ajax( {
			url :"gadgets/formular/admin/edit_field.php",
			global :false,
			async :false, //muss bestehen bleiben damit die Parameter nach success übergeben werden (new_textfield)
			data :( {  id : id }),
			type :"POST",
			dataType :"html",
			beforeSend : function() { 
				//$('#modal_edit_formular').modal({ allowMultiple: true, observeChanges : true, autofocus: false, closable: false });
				$('#modal_edit_formular').modal('show'); 
				$('#modal_edit_formular > .content').html('<div class=\'ui segment\'><br><br><br><br><br><br><div class=\'ui active inverted dimmer\'><div class=\'ui text loader\'>...wird geladen</div></div><br><br><br><p></p></div>'); },
			success  : function(data) { $('#modal_edit_formular > .content').html(data);  }
  });	
}

//Nach erfolreicher Änderung wird Feld neu geladen
function load_field(id) {
	$.ajax( {
		url :"gadgets/formular/admin/load_field.php",
		global :false,
		async :false, //muss bestehen bleiben damit die Parameter nach success übergeben werden (new_textfield)
		data :( {  id : id }),
		type :"POST",
		dataType :"html",
		success  : function(data) { $('#row_field-'+id).replaceWith(data);  call_inline_edit(); }
	});	
}
