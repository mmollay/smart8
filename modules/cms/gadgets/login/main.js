$(document).ready(function() {
	call_login();
});

function call_register() {
	
	$('#div_form_login').hide();
	$('#div_form_register').fadeIn();
	$('#email').focus();
	$('.ui.modal.login').modal('refresh');
}

function call_login() {
	$('#div_form_login').fadeIn();
	$('#div_form_register').hide()
	$('#user').focus();
	$('.ui.modal.login').modal('refresh');
}