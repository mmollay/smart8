
function afterFormSubmit(id, customTitle = 'Data has been saved') {

	// Verwenden des customTitle, falls angegeben, sonst den Standardtitel
	if (id === 'ok') {
		$('#message').message({ status: 'info', title: customTitle });
		$('.ui.modal').modal('hide');
		table_reload();
	} else {
		$('#message').message({
			status: 'error', title: 'System error: ' + id
		});
	}
}