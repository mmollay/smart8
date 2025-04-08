function after_form(value,id) {
	
	$('#modal_challenge').modal('hide');
	call_filter();
	call_list();
	
	if (value == 'error') {
		alert('Error: User_ID nicht definiert! ');
	}
	else if (value == 'failed') {
		call_lose_challenge(id);
	}
	else if (value == 'success') {
		call_success_challenge(id);
	}
	else if (value == 'multi_success' ) {
		
	}
		
}