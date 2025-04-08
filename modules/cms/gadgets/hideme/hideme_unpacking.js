$.ajax( {
		url :'gadgets/hideme/form_hideme_unpacking.php',
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
	$('#download-div').html('<br><br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui text loader\">Inhalt wird aus dem Bild gelesen...</div></div>');
}

/**
 * Ruft Paramten nach absenden der HideForm auf
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
			  if (response.errorcode =='HID-007') {
				  $('#error-div').show().html('Der Schlüssel stimmt nicht überein!');
			  }
			  else if (response.errorcode =='CRL-001') {
				  $('#error-div').show().html('Der Pfad zum Bild scheint ungültig zu sein!');
			  }
			  else {
				  $('#error-div').show().html(response.errormessage);
			  }
		  }		
		}
	} else { 
		//$('#download-div').html(nl2br_12(data));
		$('#download-div').html(data);
	}
}


function nl2br_12(str) {
    if(typeof(str)=="string") return str.replace(/(\r\n)|(\n\r)|\r|\n/g,"<BR>");
    else return str;
}