/*
 * Powerd by SSI - Martin Mollay martin@ssi.at
 * Update: 20.01.2017 *
 *   
 * Zusatz js für das anlegen von Seiten 
 */

function onload_site_form(){
	
	var xhr = null;
	
	$('#site_title').keyup( function() {
		
		if( xhr != null ) {
	        xhr.abort();
	        xhr = null;
		}
		
		xhr = $.ajax( {
			url      : 'admin/inc/call_url_name.php',
			data     : ({ url_name:$('#site_title').val() }),
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('#site_url').val(data);			
				$('#menu_text').val($('#site_title').val() );
			}
		});
		
	});

	$('#site_url').change( function() {
		
		$.ajax( {
			url      : 'admin/inc/call_url_name.php',
			data 	 :({'url_name':$('#site_url').val()}),
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('#site_url').val(data);	
			}
		});
	});
}

//Funktions nach speichern der Site-options
function update_site_form(data){
 
 
 
	if (data == 'update'){
			
		$('body').toast({ message: 'Seitenoptionen gespeichert!', class : 'info' });
				
		$('#option_site').modal('hide');
		//Refresh - Page
		CallContentSite($('.site_id').attr('id'));
		
		//$(location).attr('href','index.php?site_select='+$('.site_id').attr('id'));

	}
	else if (data == 'update_list'){
		$('body').toast({ message: 'Seitenoptionen gespeichert!', class : 'info' });
		
		table_reload();
		$('#button_allsites').click();
		$('#modal_form').modal('hide');
	
		
	}
	else if (data > 0 ) {
		var site_id = data;
		$('body').toast({ message: 'Eine neue Seite wurde einrichtet!', class : 'info' });
		$('#option_site').modal('hide');
		
		CallContentSite(site_id);
		//$(location).attr('href','index.php?site_select='+site_id);		
	}
	else {
		alert(data);
		$('body').toast({ message: 'Seite konnte nicht angelegt werden', class : 'error' });
	}	
	
}