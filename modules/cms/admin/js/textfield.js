/*
 * Powred by SSI - Martin Mollay martin@ssi.at
 * Update: 30.11.2016 - Delete,archive,move form elements
 * Update: 13.04.2017 - Splitterfunction includet 
 */

$(document).ready(function(){

	/***************************************************************************
	 * Module in die Seite hineinziehen
	 **************************************************************************/
	$('.new_module').draggable({
		
	     
		connectToSortable: ".sortable",
		 start: function( event, ui ) {
			 call_edit_modus('off');
			 $('.new_module').removeClass('tooltip');
			 $('.new_module').removeClass('move_draggable');
             $(this).addClass('move_draggable');
             //$('#left_0').css({'padding-top':'50px','padding-bottom':'50px'});
             //Schließen des Popups
             //$('.elements').popup('hide');
        },
        
        stop: function ( event, ui ) {
	        	//alert('Test');
	        	$(this).removeClass('ui message');
	        	//$('#left_0').css({'padding-top':'0px','padding-bottom':'0px'});
	        	call_edit_modus('on');
	        	$('.show_admin_line').hide();
        },

		 //helper: "clone",
	      helper: function( event ) {
	          return $( "<div class='ui basic label' style='width:200px; height:80px;'><br>Ziehe mich<br>zum gewünschten Platz<br></div>" );
	        },
		 revert: "invalid",
		 cursor: "move"
	});
	
	$( ".smart_content_body" ).droppable({
		accept: ".new_module",
		tolerance: 'pointer',	
	});
	
	SetSortable();
	SetNewTextfield();
});

//Aufruf, des Designers für die Darstellung der Elemente
function call_element_setting(ID,element) {
	$.ajax({
		beforeSend: function () {
			//$('#call-loader').show()
			$('.sidebar-element-setting').sidebar('show');
			$("#sidebar-element-setting-content").html('<div class="ui segment"><br><div class="ui active inverted dimmer"><div class="ui active slow double text blue loader"><br>Einstellungen laden</div></div><br><br><br></div>');
		},
		success:    function(data){
			
			setTimeout(
					  function() 
					  {
						  $("#sidebar-element-setting-content").html(data);
							$('.sidebar-design').sidebar('hide'); //,.sidebar-elements
							//$('.sidebar-element-setting').sidebar('is visible',$('#call-loader').hide());
							//$('.sidebar-element-setting').sidebar('show');
					  }, 500);
		},				
		url :"admin/ajax/form_gadget.php",
		global   : false,
		data : ({ update_id : ID, name : element }),
		type     : "POST",	
		dataType : "html"
	});
}


//Delete, Archive, Move für Felder (Elemente)
function handle_field(action,ID) {
	
	//Löschen wird ohne Rückfrage ausgeführt
	if (action == 'delete') {
		$.ajax( {				
			url :"admin/ajax/form_action2.php",
			global   : false,
			data : ({ id : ID, action : action }),
			type     : "POST",	
			dataType : "script"
		});
	}
	else {
		$.ajax( {				
			url :"admin/ajax/form_action.php",
			global   : false,
			data : ({ ID : ID, action : action }),
			type     : "POST",	
			dataType : "html",
			beforeSend: function () {
				$('#modal_small_content').html('Inalt wird geladen...');
				$('#modal_small').modal({autofocus: false, observeChanges : true}).modal('show');
			},
			success:    function(data){  
				$("#modal_small_content").html(data);  
				$('#modal_small a[data-tab]').on('click', function () { $('#modal_small').modal('refresh'); });		
			},
		});
	}
}

function layer_back_archive(id) {
	$.ajax( {				
		url :"admin/ajax/layer_back_archive.php",
		global   : false,
		data : ({ id : id }),
		type     : "POST",	
		dataType : "html",
		success:    function(data){  
			$('.list_archive').modal('hide');
			$('#ProzessBarBox').message({ status:'info', title: 'Seite wird neu geladen!' });
			location.href='index.php?site_select='+data		
		},
	});
}