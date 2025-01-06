function call_form_bill(update_id, clone) {
	
	//Content wird sichtab gemacht in font.css unsichtbar gemacht
	$('.hide_content').css( {'visibility' :'visible'}).delay(200).fadeIn();
	$('#client_id').focus(); 
	$('#add_client').hide();
	$('#mod_client').hide();

	//versteckt den Tab wenn Angebot gewählt wurde
	set_document_settings(true);
	
	var set_client = $('#dropdown_client_id').dropdown('get value');
	
	// Darstellung des Ausgabefensters fuer die erzeuten Positionen
	$('#position_list').css({ 'min-height':'130px','overflow':'auto'});
	
	// aktuelle Liste auslesen	
	var position_content = $.ssi_ajax({ url : "ajax/add_cart.php", data : ({ update_id : update_id, clone : clone }) });
	
	// Erzeugte Positonen uebergeben
	$('#position_list').html(position_content).css({ 'text-align':'center'});
	
	$('#save_desc').click( function() {
		var call_request = $.ssi_ajax({ url: 'ajax/save_bill_desciption.php', data: ({ subject: $('#description').val() }) });
		if (call_request == 'error') 
			$('#message').message({status:'error', title:'Fehler beim Speichern - Firma scheint nicht gewählt zu sein' });
		else 
			$('#message').message({status:'info', title:'Der \"Betreff\" wurde erfolgreich gespeichert' });
	});
	
	$('#anonym').click( function() {
		if ($('#anonym').attr('checked') == 'checked') {
			$('#row_client_id').hide();
			$('#row_client_number').hide();
			$('#row_company_2').hide();
			$('#row_title').hide();
			$('#row_gender').hide();
			$('#row_firstname').hide();
			$('#row_secondname').hide();
			$('#row_street').hide();
			$('#row_zip').hide();
			$('#row_country').hide();
			$('#row_post').hide();
			$('#row_web').hide();
			$('#row_uid').hide();
		}
		else {
			$('#row_client_id').show();
			$('#row_client_number').show();
			$('#row_company_2').show();
			$('#row_title').show();
			$('#row_gender').show();
			$('#row_firstname').show();
			$('#row_secondname').show();
			$('#row_street').show();
			$('#row_zip').show();
			$('#row_country').show();
			$('#row_post').show();
			$('#row_web').show();
			$('#row_uid').show();
			
		};
	});
	
	/*******************************************************************************
	 * CLIENT - CHECK NEW NUMBER
	 ******************************************************************************/
	$('#new_client_number').click( function () { 
		var content = $.ssi_ajax({ url : "ajax/new_client_number.php" });
		$('#client_number').val(content);
	});
	
	
	$('#rem_client').click( function () { 
		clear_all_fields();
		$('#dropdown_client_id').dropdown('clear');
		$('#new_client_number').click();
		$('#company_1').focus();
	});
	
	
	/*******************************************************************************
	 * CLIENT - CHOOSE CITY
	 ******************************************************************************/
	$('#zip').change( function(){
		//Auslesen der Stadt
		var content = $.ssi_ajax({ url : "ajax/call_city.php", data : ({ zip : $('#zip').val() }) });
		$('#city').val(content);
	});
	
	if (set_client) {
		$('#add_client').hide();
		$('#mod_client').show();
	}
		
	/*******************************************************************************
	 * CLIENT - INSERT AND UPDATE
	 ******************************************************************************/
	$('#add_client, #mod_client').click( function () { 
		
		// Werte auslesen und in Datenbank uebertragen
		if ($('#post').attr('checked') == true) var post = 1;
		else var post = 0;
		var modus = this.id;
		var check_add_client = $.ssi_ajax({ url : "ajax/client_add.php",
			data     : ({ 
				modus : modus,
				client_id : set_client,
				client_number:$('#client_number').val(),
				company_1: $('#company_1').val(),
				company_2: $('#company_2').val(),
				gender: $('#gender').val(),
				title: $('#title').val(),
				firstname: $('#firstname').val(),
				secondname: $('#secondname').val(),
				street: $('#street').val(),
				city: $('#city').val(),
				zip: $('#zip').val(),
				country: $('#country').val(),
				tel: $('#tel').val(),
				fax: $('#fax').val(),
				mobil: $('#mobil').val(),
				email: $('#email').val(),
				web: $('#web').val(),
				uid: $('#uid').val(),
				post: post,
				commend: $('#commend').val(),
				map_page_id : $('#map_page_id').val(),
				map_user_id : $('#map_user_id').val()
			})
			});
		
		if (check_add_client == 'empty_client_number') {
			$('#message').message({status:'error', title:'Kundennummer existiert bereits' });
			$('#client_number').focus(); 
		}
		// Verarbeitung des Response
		else if (check_add_client == 'double_client_number') {
			$('#message').message({status:'error', title:'Kundennummer existiert bereits' });
			$('#client_number').focus();
		}
		else if (check_add_client == 'double_company_name') {
			$('#message').message({status:'error', title:'Firmenname existiert bereits' });
			$('#company_1').focus();
		}
		else if (check_add_client) { 
			if (modus == 'mod_client') 
				$('#message').message({ title:'Der Kunde "'+$('#company_1').val()+'" wurde aktualisiert' }); 
			else {
				$('#message').message({ title:'Der Kunde "'+$('#company_1').val()+'" wurde aufgenommen' });
				
				//$('#client_id').prepend('<option selected=\'selected\' value=\''+check_add_client+'\'>'+$('#company_1').val()+'</option>');
				$('#dropdown_client_id').find('.menu').append('<div class=\'item\' data-value=\''+check_add_client+'\'>'+$('#company_1').val()+'</div>');
				$('#dropdown_client_id').dropdown('refresh');
				$('#dropdown_client_id').dropdown('set selected', check_add_client );

				$('#combo_client_id').val($('#company_1').val());
				
				$('#mod_client').show();
				$('#add_client').hide();
				
			}
		}
	});

	/*******************************************************************************
	 * CLIENT - SELECT DATA Select fuer Produktauswahl und hineinladen in die
	 * jeweiligen Felder
	 ******************************************************************************/	
	//$("#client_id").chosen().change(function(){ 
	$('#dropdown_client_id').checkbox('setting', 'onChange', function () {

		//$('#combo_client_id').focus( function () {
		
		// Auslesen der Werte und hineinladen in die jeweiligen Felder
		$('#mod_client').show();
		$('#add_client').hide();
		
		var id_client = $('#dropdown_client_id').dropdown('get value');
		
		// Felder zuruecksetzen
		if (!id_client) {
			clear_all_fields();
		};
		
		//Artikelinhalt auslesen und direkt an das Formular übergeben
		$.ajax({
			url : 'ajax/call_client_values.php',
			data: { client_id : id_client },
			global : false,
			type : "POST",
			dataType : "script"
		});
		
		if ($('#post').val() == '1' ) $('#post').attr('checked', true);
		else $('#post').attr('checked', false);
	});

	/*******************************************************************************
	 * CLIENT - SHOW ADD-Button After INCLUDE the first value
	 ******************************************************************************/
	$('#company_1,#firstname,#secondname').change( function () {
		if (! $('#dropdown_client_id').dropdown('get value') ) { 
			$('#add_client').show();
		}
	});
	
	//Falls Client User naträglich "adden" will
	if ( ! $('#dropdown_client_id').dropdown('get value') && ( $('#company_1').val() || $('#firstname').val() ) ) $('#add_client').show();

	/*******************************************************************************
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * ARTICLE * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 */
	$('#mod_temp, #add_art, #cancel_art').hide();
	
	/*******************************************************************************
	 * ARTICLE - SELECTED article LOAD values in FORM
	 ******************************************************************************/
	$('#select_temp').change( function () {
		// Felder zuruecksetzen
		if ($('#select_temp').val() == '') {
			
			$('#mod_temp').hide();
			
			$('#art_nr').val(''); 
			$('#art_title').val('');
			$('#format').val('');
			$('#count').val('');
			$('#art_text').val('');
			$('#account').val('');
			$('#netto').val('');		
			$('#add_art').hide();
			$('#cancel_art').hide();
		}
		else {
			//Artikelinhalt auslesen und direkt an das Formular übergeben
			$.ajax({
				url : 'ajax/call_temp_article.php',
				data: { temp_id : $('#select_temp').val() },
				global : false,
				type : "POST",
				dataType : "script"
			});

			$('#mod_temp').show();
			// In den Warenkorb-Button
			$('#add_art').show();
			$('#cancel_art').show();
		}
		
	});

	/*******************************************************************************
	 * ARTICLE - ADD Article_template
	 ******************************************************************************/
	$('#add_temp, #mod_temp').click( function () { 		
		var modus = this.id;
	
		// Werte auslesen und in Datenbank uebertragen
		var check_add_template = check_add_client = $.ssi_ajax({ url : "ajax/save_article.php",
			data     : ({
				// company_id: $('#company_id').val(),
				modus: modus,
				temp_id: $('#select_temp').val(),
				format: $('#format').val(),
				count: $('#count').val(),
				art_nr: $('#art_nr').val(),
				art_title: $('#art_title').val(),
				art_text: $('#art_text').val(),
				account: $('#account').val(),
				netto:	$('#netto').val(),
			})
		});
		
		// Verarbeitung des Response
		if (check_add_template == 'empty_art_nr') {
			$('#message').message({status:'error', title:'Artikelummer ist leer' });
			$('#art_nr').focus();
		}
		else if (check_add_template == 'empty_title') {
			$('#message').message({status:'error', title:'Titel ist leer' });
			$('#art_title').focus();
		} 
		else if (check_add_template == 'double_art_nr') {
			$('#message').message({status:'error', title:'Artikelummer existiert bereits' });
			$('#art_nr').focus();
		}
		else if (check_add_template == 'double_title') {
			$('#message').message({status:'error', title:'Titel existiert bereits' });
			$('#art_title').focus();
		}
		else if (check_add_template == 'ok') {  
			if (modus == 'mod_temp') 
				$('#message').message({ title:'Der Artikel "'+$('#art_title').val()+'" wurde aktualisiert' }); 
			else if (modus == 'add_temp')  {
				$('#message').message({ title:'Der Artikel "'+$('#art_title').val()+'" wurde aufgenommen' }); 
				$('#art_nr').focus();
				//$('#select_temp').append('<option selected=\'selected\' value=\''+check_add_template+'\'>'+$('#art_nr').val()+'-'+$('#art_title').val()+'</option>');
				add_val_dropdown('select_temp',$('#art_nr').val(),$('#art_title').val());
			}
		}
		else {
			$('#message').message({ title:'Fehler beim speichern' });
		}
	});

	/*******************************************************************************
	 * ARTICLE - ADD in CARD
	 ******************************************************************************/
	$('#add_art, #cancel_art').click( function () { 
		
		if (this.id == 'add_art') {
			// Werte auslesen und in Datenbank uebertragen
			var read_templates = $.ssi_ajax( {
				url      : "ajax/add_cart.php",
				data     : ({ 
					add_article: true,
					update_temp:  $('#update_temp').val(),
					temp_id: $('#select_temp').val(),
					format: $('#format').val(),
					count: $('#count').val(),
					art_nr: $('#art_nr').val(),
					art_title: $('#art_title').val(),
					art_text: $('#art_text').val(),
					account: $('#account').val(),
					netto:	$('#netto').val(),
				})
			});
			
			$('#position_list').html(read_templates);
			
		}
		
		$('#art_nr').val(''); 
		$('#art_title').val('');
		$('#format').val('');
		$('#count').val('');
		$('#art_text').val('');
		$('#account').val('');
		$('#netto').val('');
		$('#update_temp').val('');
		$('#select_temp').val('');
		
		$('#mod_temp').hide();
		$('#add_art').hide();
		$('#cancel_art').hide();
		return false;
		
	});

	/*******************************************************************************
	 * ARTICLE - SHOW ADD-Button After INCLUDE the first value
	 ******************************************************************************/
	$('#art_title').change( function () {
		if (!$('#select_temp').val()) {
			$('#add_art').show();
			$('#cancel_art').show();
		}
	});

}

/*******************************************************************************
 * ARTICLE - Delete temp-Article
 ******************************************************************************/
function fu_del_temp_article(id) {
	$(document).ready(function() {
		var read_templates = $.ssi_ajax( { url : "ajax/add_cart.php", data : ({ rm_article: id }) });
		$('#position_list').html(read_templates);
	});
}

/*******************************************************************************
 * ARTICLE - Edit temp-Article
 * update 11.04.2017
 ******************************************************************************/
function fu_edit_temp_article(id) {
	$.ajax({
		url : 'ajax/read_cart_article.php',
		data: { id: id },
		global : false,
		type : "POST",
		dataType : "script"
	});
	
	$('#update_temp').val(id); 		
	$('#add_art').show();
	$('#cancel_art').show();	
}

/********************************************************************************
 * Setzt diverse Handlungen bei Änderung des Documents
 ********************************************************************************/
function set_document_settings(first_load = false ) {
	var document = $('input[name=document]:checked').val();
	var company_id =  $('#faktura_company_id').val();
	
	if (document !='rn') $('.tabular.menu.small>#4').hide();
	else $('.tabular.menu.small>#4').show();
	
	//Wird beim ersten laden nicht ausgeführt, die soll also nur bei switch geändert werden
	if (!first_load) {
		//Aktuelle Folgenummer abrufen
		var nr = $.ssi_ajax({ url : "ajax/call_bill_number.php", data : ({ document : document, company_id : company_id }) });
		$('#bill_number').val(nr);
	}
	
}

/******************
 * Felder löschen 
 *******************/
function clear_all_fields() {
	$('#mod_client').hide();
	//$('#client_id').val(''); 
	$('#client_number').val('');
	$('#company_1').val('');
	$('#company_2').val('');
	$('#gender').val('');		
	$('#title').val('');
	$('#firstname').val('');
	$('#secondname').val('');
	$('#street').val('');
	$('#city').val('');
	$('#zip').val('');
	$('#country').val('');
	$('#tel').val('');
	$('#email').val('');
	$('#tel').val('');
	$('#fax').val('');
	$('#mobil').val('');
	$('#web').val('');
	$('#uid').val('');
	$('#commend').val('');
	$('#post').attr('checked', false);
}