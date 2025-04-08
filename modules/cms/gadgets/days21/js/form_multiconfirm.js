function (value,id) {
	$('#modal_challenge').modal('hide');
	call_filter();
	call_list();
	$('.tooltip').popup();
	if (value == 'failed') {
		call_form(id, 'Dein Feedback', 'gadgets/days21/ajax/form_comment_cancel.php', ['500', '300' ]);
		call_lose_challenge(id);
	}

	if (value == 'success') {
		call_success_challenge(id);
	}
}