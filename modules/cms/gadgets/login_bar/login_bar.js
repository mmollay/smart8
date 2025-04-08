
function user_bar(verify_key = '', color = '', button_text = '', icon = '') {
	$.ajax({
		type: 'POST',
		url: 'gadgets/login_bar/userbar.php',
		dataType: 'html',
		data: {verify_key: verify_key, color: color, button_text: button_text, icon: icon},
		success: function(data) { 
			$('.userbar').html(data); 
			$('.tooltip').popup({position : 'top center'}); 
		},
		error: function(xhr, status, error) {
			console.error("An error occurred: " + status + " - " + error);
		}
	});
}
	
function logout(){
	$.ajax({
		type: 'POST',
		url: 'gadgets/login_bar/logout.php',
		dataType: 'script'
	});	
}

function login_save(){
	$('#modal_login.content').html('<iframe src="gadgets/login/"  style="border: 0; width:100%; height:500px;" marginwidth="0" >'); 
	$('.ui.modal.login').modal('show');
}

function bazar_call_form(path){
	$.ajax( {
		url :'gadgets/'+path+'.php',
		global   : false,
		data: ({ ajax: true }),
		type     : 'POST',	
		dataType : 'html',
		beforeSend: function () {
			$('.ui.modal.login').modal('show');
			$('.ui.modal.login > .content').html('<div class="ui active inverted dimmer"><div class="ui text loader">Wird geladen...</div></div><p></p><br><br><br>');
		},
		success:  function(data){ 
			$('.ui.modal.login > .content').html(data); 
			$('.ui.modal.login').modal('refresh');
		}
	});
}