function afterFormSubmit(id, customTitle = 'Data has been saved') {

	// Verwenden des customTitle, falls angegeben, sonst den Standardtitel
	if (id === 'ok') {
		$('#message').message({ status: 'info', title: customTitle });
		$('#modal_form, #modal_form_clone').modal({ onHidden: function () { $(this).find('.content').empty(); } }).modal('hide');

		table_reload();
	} else {
		$('#message').message({
			status: 'error', title: 'System error: ' + id
		});
	}

}
