/**
 * 
 */
$(document).ready(function() {
	$('.menu .item') .tab();
	
	$('.show_issues').click( function(){ 
		$('#modal_issues').modal('show');
		$.ajax( {
			url      : "ajax/content_list_issues.php",
			global   : false,
			//async    : false,
			type     : "POST",
			data     : ({ list_filter : this.id, load_ajax: true, show:'all' }),
			beforeSend: function(){
				$('#modal_issues>.content').html("<div class='ui message'><br><div class='ui active inverted dimmer'><br><div class='ui text loader'>Inhalt laden...</div><br><br></div><br><br></div>");
			},
			success  : function (content) {
				$('#modal_issues>.content').html(content);
				//$('#modal_issues').modal({observeChanges:true});
				//$("#window_list").dialog({position: 'top', modal :true,  title: "Ausgaben", width: '90%' }).html(content);	 
				},
			dataType : "html"
		});
	});
	
	$('.show_earnings').click( function(){ 
		$('#modal_earnings').modal('show');
		$.ajax( {
			url      : "ajax/content_list_earnings.php",
			global   : false,
			//async    : false,
			type     : "POST",
			data     : ({ list_filter : this.id, load_ajax: true, show:'all' }),
			beforeSend: function(){
				$('#modal_earnings>.content').html("<div class='ui message'><br><div class='ui active inverted dimmer'><br><div class='ui text loader'>Inhalt laden...</div><br><br></div><br><br></div>");
			},
			success  : function (content) {
				$('#modal_earnings>.content').html(content);
				//$('#modal_issues').modal({observeChanges:true});
				//$("#window_list").dialog({position: 'top', modal :true,  title: "Ausgaben", width: '90%' }).html(content);	 
				},
			dataType : "html"
		});
	});
	
});