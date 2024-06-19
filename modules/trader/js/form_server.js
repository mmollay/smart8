$(document).ready(function () {

});

function after_form_server(id) {

	if (id == 'ok') {
		$('#message').message({ status: 'info', title: 'The account has been saved!' });
		$('#modal_form').modal('hide');
		table_reload();
	} else {
		$('#message').message({
			status: 'error', title: 'System error:' + id
		});
	}

}
