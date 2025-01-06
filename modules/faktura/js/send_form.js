function set_mahung(id) {
		$.ssi_ajax({ url: 'inc/remind_manually.php', data: ({ bill_id: id }) });
		$('#ProzessBarBox').message({ type:'info',title:'Info', text: 'Mahnung-Stufe wurde ohne Mailversand gesetzt!' });
		table_reload();
		$('#modal_form_send').modal('hide');
}