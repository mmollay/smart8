$.ajax( {
		url :'gadgets/hideme/form_hideme_produce.php',
		global :false,
		type :"POST",
		dataType :"html",
		beforeSend: function(data){
			$('#container-hideme').html("<div class='ui icon message'><i class='notched circle loading icon'></i><div class='content'><div class='header'>Content wird geladen</div></div></div>"); 
		},
		success: function(data) {  
			$('#container-hideme').html(data); 
		},
});

$('#button-show-container-hideme').click ( function () {
	$('#button-show-container-hideme,#error-div,#segment-download').hide();
	$('#container-hideme').show();
});

/**
 * Rueft Parameter bevor fertig geladen ist
 */
function function_hideme_beforeSend(){
	$('#segment-download').show(); 
	$('#container-hideme').hide();
	$('#download-div').html('<br><br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui text loader\">Bild wird erzeugt...</div></div>');
}

/**
 * Ruft Parameter nach absenden der HideForm auf
 * @param data
 */
function function_hideme_success(data){
	 $('#button-show-container-hideme').show();
	if(data.substr(0,1) == '{'){	
		var response=jQuery.parseJSON(data);
		if(typeof response =='object')
		{
		  if (response.status == 'error') {
			  $('#segment-download').hide();
			  if (response.errorcode =='HID-002') {
				  $('#error-div').show().html('Der Pfad zum Bild scheint ungültig zu sein!');
			  }
			  else if (response.errorcode =='HID-003') {
				  $('#error-div').show().html('Password wurde nicht angeben!');
			  }
			  else if (response.errorcode =='HID-004') {
				  $('#error-div').show().html('Es wurde kein Text angegeben!');
			  }
			  else {
				  $('#error-div').show().html(response.errormessage);
			  }
		  }		
		}
	} else { 
		if ($('#img_name').val()) { name = $('#img_name').val() } else { name = 'secret'; } 
		$('#download-div').html('<img class=\"ui small bordered rounded image\" src=\"' +  $('#img_url').val() + '\" /><br><a class=\"button icon ui blue\" href=\"' +  data + '\" download=\"'+name+'.png\" ><i class=\"icon download\"></i> Herunterladen</a>');
	}
}