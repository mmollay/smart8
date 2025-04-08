/************************************************************************************
 * Funcion für Seitenoptionen - ruft ueber ajax array-Werte fuer jeweilige Seite auf
 ************************************************************************************/
function call_site_options(id) {

	// Array - Content auslesen
	var arrayFromPHP = $.ajax( {
		url :"admin/ajax/site_option_values.php",
		global :false,
		type :"POST",
		data :( {
			id :id
		}),
		dataType :"text"
	}).responseText;

	// Umwandeln von Array auf json
	var arrayFromPHP = $.parseJSON(arrayFromPHP);

	// Auslesen der Values aus dem Array
	var matchcode = arrayFromPHP.matchcode;
	var title = arrayFromPHP.title;
	var title_grafic = arrayFromPHP.title_grafic;
	var meta_text = arrayFromPHP.meta_text;
	var meta_title = arrayFromPHP.meta_title;
	var meta_keywords = arrayFromPHP.meta_keywords;
	var menu_id       = arrayFromPHP.menu_id;
	var profil_id  = arrayFromPHP.profil_id;

	$('#site_id').val(id);
	$('#site_matchcode').val(matchcode);
	$('#site_title').val(title);
	$('#title_grafic').val(title_grafic);
	$('#meta_title').val(meta_title);
	$('#meta_text').val(meta_text);
	$('#meta_keywords').val(meta_keywords);
	$('#menu_id').val(menu_id);
	$('#profil_id').val(profil_id);

	// Button beim Speichern umschreiben
	$('#option_button').val('Speichern');

	$('#form1').fadeIn().dialog('open');
	$('#seite_matchcode').focus();
	return false;
}


/************************************************************************************
 * Function: Leoschen der jeweiligen Seite 
 ************************************************************************************/

function DelConfirmFunctionSite(id,path,dialogId) {
	
	// Dialogfenster zum löschen von Layern, Textfeldern und Seiten
	$('#'+dialogId).dialog( {
		resizable :false,
		height :170,
		modal :true,
		buttons : {
			'L&ouml;schen' : function() {
				var content = $.ajax( {
					url : path,
					global :false,
					type :"POST",
					data :( {id :id}),
					dataType :"html"
				}).responseText;
			//alert(content);		
			// Seite neu laden "content" = id von Startseite
			$(location).attr('href','index_admin.php?id='+content)

		},
		Abbrechen : function() {
			$(this).dialog('close');
		}
		}
	});
}
