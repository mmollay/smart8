$(document).ready(function() {
	call_filter();
	call_list();
	$("#add_button").click( function() { 	
		call_modal_form()
	});	
	
});

if (!path21) var path21 = 'gadgets/days21';

function laod_content (id,path){
	var content = $.ajax( {
		url : path,
		global :false,
		type :"POST",
		data :( {id :id, ajax:true}),
		dataType :"html"
	}).responseText;
	return content;
}

//Dialogfenster zum loeschen von Layern, Textfeldern und Seiten
function show_details_save(id) {
	$('#window').dialog( {
		//title: 'Details der Challenge',
		resizable :false,
		height :600,
		width: 800,
		modal :true,
		buttons : {
			'Laden' :     function() { $(this).dialog().html(laod_content(id,path)); },	
			'Schließen' : function() { $(this).dialog('close'); }
		}
	}).html(laod_content(id,path21+'/show_details.php'));
}


function show_details(id) {
	$.ajax( {
		url      : path21+'/show_details.php',
		global   : false,
		async    : false,
		type     : "POST",
		data     : ({ id : id, ajax : true}),
		dataType : "html",
		success  : function(data) { 
			$("#modal_content").html(data);
			//$("#modal_challenge").modal('setting', 'can fit', true);
			$("#modal_challenge").modal('show');
		},
	});
}




//Liste wird neu geladen
function call_list() {
	$.ajax( {
		url :path21+'/call_list.php',
		global :false,
		type :"POST",
		data :( {ajax : true }),
		dataType :"html",
		beforeSend: function(data){
			$('#challenge_list2').html("<div class='ui icon message'><i class='notched circle loading icon'></i><div class='content'><div class='header'>Challenges werden geladen</div></div></div>"); 
		},
		success: function(data) {  
			$('#challenge_list2').html(data); 
			$('.ui.dropdown') .dropdown({ on: 'hover' });	
			$('.tooltip-edit').popup({position : 'right center'});
			$('.tooltip').popup({position : 'top center'});
		},
	})
}

//Call Filter with new value form db
function call_filter() {
	$('.tooltip').popup('hide all');
	$.ajax( {
		url :path21+'/call_filter.php',
		global :false,
		type :"POST",
		data :( {ajax : true }),
		dataType :"html",
		success: function(data) {  
			$('#day21_filter').html(data);
			$('.ui.checkbox').checkbox();
			//$('.ui.accordion').accordion();
			$('.dropdown').dropdown({ on: 'hover' });
	
			$('.select_action').click(function() {
				$.post(path21+'/call_search.php', {'list_search':$('#list_search').val(), 'select_action':this.id, 'show_all': $('#show_all').attr('checked') });
				call_filter();
				call_list();
			})

			$("#show_all").bind('change', function(){
				$.post(path21+'/call_search.php', {'show_all': $('#show_all').attr('checked'),'list_search':$('#list_search').val() });
				call_filter();
				call_list();
			});
			
			$("#submit_button").click( function(){
				$.post(path21+'/call_search.php', {'list_search':$('#list_search').val(), 'select_action':'list_all', 'show_all': $('#show_all').attr('checked') });
				call_filter();
				call_list();
			});
			
			$("#submit").submit( function(){
				$.post(path21+'/call_search.php', {'list_search':$('#list_search').val(), 'select_action':'list_all', 'show_all': $('#show_all').attr('checked') });
				call_filter();
				call_list();
			});		
		}
	})
	
}