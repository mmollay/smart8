$(document).ready(function() {
		
		$.ajax( {
			url :'gadgets/wtm/ajax/element_articlelist.php',
			global :false,
			type :'POST',
			data :( { group_id : group_id }),
			dataType :'html',
		    beforeSend : function() { $('#call_events').html('<div class=\"ui segment\"><br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui text loader\">Veranstaltungen werden geladen</div></div><p></p></div>'); },
			success: function(data) { $('#call_events').html(data); $('#portal_login_bar').css({'visibility':'visible'}).fadeIn();  }
		});
});

function button_logout_account(){
	$.ajax( {
		url :'gadgets/wtm/inc/logout_user.php',
		data :( {ajax : true }),
		type :'POST',
		dataType :'html',
		success: function(data) { table_reload(); userbar_reload(); }
	});
}


function button_login_account(){
	$.ajax( {
		url :"gadgets/wtm/ajax/form_login.php",
		type: 'POST',
		success: function(data){ 
			$("#modal_content_login").html(data);		
			$("#modal_login").modal('show');
		}
	});
}

function userbar_reload(){
	$.ajax( {
		url :'gadgets/wtm/ajax/element_userbar.php',
		type :'POST',
		dataType :'html',
		data : ({ reload :true}), //Parameter für die Sichtbarkeit notwendig
		success: function(data) { $('#portal_login_bar').html(data); }
	});
}

function filter_year(year) {
	$.ajax( {
		url :'gadgets/wtm/inc/set_filter.php',
		type: 'POST',
		data: ({'filter_year':year}),
		success:    function(data){ 
			if (!year) year ='all';
			table_reload();
			$('.tab_year').removeClass('active');
			$('#'+year+'.tab_year').addClass('active');
		}
	});
}

