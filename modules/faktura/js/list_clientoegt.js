$(document).ready(function() {


});


/*
   * Rechungsgenerator zum erzeugen einer Rechung des jeweiligen Kunden laut Einstellungen in seinem System
   */
function call_generate_bill(ID, setting) {

	var content = $.ajax({
		url: "oegt/generate_bill.php",
		global: false,
		async: false,
		type: "POST",
		data: ({ id: ID, setting: setting }),
		dataType: "html",
		beforeSend: function() { $('#window_progress').dialog({ width: '300px', height: '200', modal: true }).html('<div align=center style=\"font-size:12px\"><br><img src=\"images/load.png\"><br>Rechnung(en) in Erzeugung</div>'); },
		success: function(data) {
			//if (!data) $('#window_progress').dialog('close'); 	
			//else 

			$('#window_progress').html("<div align=center><br>" + data + "</div>");
			var oTable = $('#list_list').dataTable();
			oTable.fnClearTable();
		}
	}).responseText;
}
