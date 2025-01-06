	$(document).ready(function() {
		
		$("#list_filter").bind('change keyup', function(){
			$.post('inc/call_filter.php', {'list_filter':$('#list_filter').val(), 'table':'bills' });
			table_reload();
			$('#count_open_mail').load('ajax/call_count_open_mail.php');
		});
		
		$("#submit").submit( function(){
			$.post('inc/call_search.php', {'list_search':$('#list_search').val(), 'table':'bills' });
			table_reload();
			$('#count_open_mail').load('ajax/call_count_open_mail.php');
			return false;
		});
		
		$("#generate_bills").click( function() {
			call_pdf('all','first','');
		});
		
		$("#send_bills").click( function() {
			send_pdf('all');
			table_reload();			
		});
	
		/*
		 * Open Bill-Form
		 */
		if (add_bill) call_bill_form();
		
		
		/*
		 * Buttonfunktion zum anlegen einer neuen Rechnung
		 */
		
		$("#add_bill").click( function() { 
			call_bill_form();
		});
	});
	

	
	/*
	 * FUNCTION - löschen der Details der Rechnung
	 */
	function del_details(ID){
		$(document).ready(function() {
			var content = $.ajax( {
				url      : "ajax/bill_delete_details.php",
				global   : false,
				async    : false,
				type     : "POST",
				data     : ({ bill_id : ID }),
				dataType : "html"
			}).responseText;
			$('#count_open_mail').load('ajax/call_count_open_mail.php');
		})
	}
	
	function storno_bill(ID){		
			$.ajax( {
				url      : "ajax/bill_storno.php",
				global   : false,
				//async    : false,
				type     : "POST",
				beforeSend : function(){  
					if(confirm("Stornieren?")) {
						return true;
					} else {
						return false;
					}
				},
				success: function(){ 
					table_reload();
					},
				data     : ({ bill_id : ID }),
				dataType : "html"
			});
	}
	
	
	/*
	 * FUNCTION - Auruf des Fensters (Einnahme erstellen)
	 */
	function call_bill_form(ID,clone) {
		call_form(ID,'Rechnung bearbeiten','ajax/form_bill.php',['900','700'],clone);
	}	
		
	/*
	 * FUNCTION - Call Pdf-Bill
	 */	
	function call_pdf(ID,modus) {
		//window.location = "pdf_generator.php?bill="+ID;
		//window.location = "pdf_generator.php?bill=all";
		window.open("pdf_generator.php?bill="+ID, '_blank');
		if (modus == 'first') {
			$("#window").dialog({
				title: "Druckerstatus",
				resizable :true,
				height : 200,
				width  : 300,  //"100%", //800
				modal :true,
				close: function(ev, ui) { $("#window").html('').dialog('destroy');  },
				buttons : { 
					"Ja" : function() { 
						
						$.ajax( {
							url      : "ajax/set_status_send_after_print.php",
							global   : false,
							type     : "POST",
							data     : ({ bill: ID  }),
							dataType : "html",
							beforeSend : function() { $("#dialog_window").html('<div class="ui segment"><div class="ui active blue elastic loader"></div><br><br><br><br></div>'); },
							success  : function(data) { $("#dialog_window").html(data); },
						});
						
						$('#count_open_print').html('0');
						$(this).dialog("close");
						
						//Load Table New
						table_reload();
					}, 
					"Nein" : function() { $(this).dialog("close"); },
				}
			}).html("War der Druckervorgang erfolgreich?<br>Die Rechnungen werden bei Best&auml;tigung auf Status 'versendet' gesetzt");
		}
	}
	
	
	function call_booking(ID) {
		$.ajax( {
			url      : "ajax/book_bill.php",
			global   : false,
			type     : "POST",
			data     : ({ update_id : ID }),
			dataType : "html",
			beforeSend : function() { 
				$('.modal-booking > .content ').html ('<div class="ui segment"><div class="ui active blue elastic loader"></div><br><br><br><br></div>'); 
				$('.modal-booking').modal('show'); 
			},
			success  : function(data) { 
				$('.modal-booking > .content ').html (data); 
			},
		})
	}
	
	function call_unbooking(ID) {
		$.ajax( {
			url      : "ajax/unbook_form.php",
			global   : false,
			type     : "POST",
			data     : ({ update_id : ID }),
			dataType : "html",
			beforeSend : function() { 
				$('.modal-booking > .header ').html ('Rechnung rückbuchen');
				$('.modal-booking > .content ').html ('<div class="ui segment"><div class="ui active blue elastic loader"></div><br><br><br><br></div>'); 
				$('.modal-booking').modal('show'); 
			},
			success  : function(data) { 
				$('.modal-booking > .content ').html (data); 
			},
		});
	}
	
	//Öffnet Modal und ladet Content
	function send_pdf(ID,remind,just_send) {
		$('.ui.modal>.content').empty();
		var content = $.ajax( {
			url      : "ajax/form_send.php",
			global   : false,
			async    : false,
			type     : "POST",
			data     : ({ ID : ID, remind : remind, just_send : just_send }),
			dataType : "html"
		}).responseText;
	   
	   $('#modal_form_send > .content ').html (content);
	   $('#modal_form_send').modal('show');	
	}	
	
	function remind_back(ID) {
		$.post('inc/remind_back.php', {'bill_id':ID });
		table_reload();
	}
	
	function write_logfie(ID) {
			call_form(ID,'In Logbuch schreiben','write_logbook.php',['750','500']);	
	}	
	