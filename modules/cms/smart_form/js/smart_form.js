$(document).ready(function () {
	//	$('.ui.accordion').accordion();
	//	$('.ui.accordion').accordion({ onOpen: function (item) {  $('.modal').modal('refresh'); } });	
	//	$('.tooltip').popup({ position:'right center' });
	//	$('.tooltip-right').popup({ position:'right center' });
	//	$('.tooltip-click').popup({ on: 'click' });

	//	$('.link.remove.icon').hide();
	//	$('.ui-input').each(function () {
	//	    if ($(this).val())
	//	    		$('.link.remove.icon#icon_'+$(this).attr('id')).show();
	//	});
});



/*************************************************************
 * Functions für Finder 
 *************************************************************/


//Aktuelle Version 
function call_finder_v1(update_id, id) {
	if (id) {
		fu_open_finder('?id=' + id);
	}
	else {
		fu_open_finder('');
	}

	$('#' + id).bind('focus', function () {

		if (update_id) {
			save_value_element(update_id, id, $('#' + id).val());
		}
		//$('#show_explorer').modal('hide');
		$('#modul_finder').flyout('hide')
	});
}

//Neue Version von Finder (experimentel)
function call_finder_v2(update_id, id) {
	$('#flyout_finder').flyout({ onShow: function () { $('#hiddenVariable').val(id); } });
	$('#flyout_finder').flyout('toggle');
}

function showDialog(ID, gadget) {

	if (ID !== undefined) var add_src = '?id=' + ID + '&gadget=' + gadget;
	else add_src = '';
	fu_open_finder(add_src)
	return false;
}

function openExplorer(dir, ID) {
	if (dir !== undefined) var add_src = '?' + dir + '&id=' + ID;
	else add_src = '';
	fu_open_finder(add_src)
	return false;
}

//Open Flypout Finder
function fu_open_finder(add_src) {

	//$('#modul_finder>.content').html("<iframe src='smart_form/file_manager.php" + add_src + "' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframe(this)'></iframe>");
	$('#modul_finder>.content').html("<iframe src='../ssi_finder/index.php" + add_src + "' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframe(this)'></iframe>");
	$('#modul_finder').flyout('toggle');
}



//Open Modal Finder
function fu_open_finder_old(add_src) {
	$('#show_explorer>.content').html("<iframe src='../ssi_finder/index.php" + add_src + "' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframe(this)'></iframe>");
	$('#show_explorer').modal({
		allowMultiple: true,
		centered: false,

		onVisible: function () { },
		onHide: function () { $('#show_explorer>.content').html(''); }
	});
	//$('#show_explorer').modal('refresh');
	$('#show_explorer').modal('show');
}

function resizeIframe(obj) {
	obj.style.height = 0;
	obj.style.height = $(window).height() - 200 + 'px';
}

function resizeIframeFlyout(obj) {
	obj.style.height = 0;
	obj.style.height = $(window).height() + 'px';
}


/*************************************************************
 * Ende - Functions für Finder 
 *************************************************************/


/*
 * Ruft Content auf und uebergibt diesen an das Modal - Fenster
 */

function smart_form_del_file(name, id) {
	$.ajax({
		url: smart_form_wp + 'ajax/del_file.php',
		global: false,
		type: 'POST',
		data: ({ name: name, id: id }),
		dataType: 'html',
		success: function (data) {
			if (data == 'ok') {
				$('.single_file_upload').val('');
				$('#sort_' + id + '.uploaded-card').remove();
				if ($('.uploaded-card').length == 0) $('#message_empty_cards').show();
			}
			return false;

		}
	});
}

function del_folder() {

	$.ajax({
		url: smart_form_wp + 'ajax/del_folder.php',
		global: false,
		type: 'POST',
		dataType: 'html',
		success: function (data) {
			if (data == 'ok') $('.uploaded-cards').html('');
			return false;

		}
	});
}

function add_file(name, workpath) {
	$.ajax({
		url: smart_form_wp + 'ajax/add_file.php',
		global: false,
		type: 'POST',
		data: ({ name: name }),
		dataType: 'html',
		// beforeSend : function () { alert('test');},
		success: function (data) {
			$(data).appendTo('.uploaded-cards');
			$('.single_file_upload').val(name);
			$('.tooltip').popup({ position: 'top center' });

		}
	});
}

//Hängt in dropdown Wert an
function add_val_dropdown(key, value, name) {

	//$('#'+key).dropdown('add optionValue', value);

	var $menu = $('#dropdown_' + key).find('.menu');
	//append new option to menu
	$menu.append('<div class="item" data-value="' + value + '">' + name + '</div>');
	//reinitialize drop down
	$('#dropdown_' + key).dropdown();
	//optional, set new value as selected option
	$('#dropdown_' + key).dropdown('set selected', value);
}


function emtpy_val_dropdown(key) {
	var $menu = $('#dropdown_' + key).find('.menu');
	//append new option to menu
	$menu.empty();
}


function open_autopopup_admin() {
	$.ajax({
		beforeSend: function () {
			$('.autopopup>.content').html(`<div class="ui large basic segment"><br><br>
				<div class="ui active inverted dimmer"><div class="ui text loader">Inhalt wird geladen</div></div><p></p></div>`);
			$('.autopopup').modal({
				centered: false,
				closable: false,
				observeChanges: true,
				// dimmerSettings: { opacity: 0.4 },
				onVisible: function () {
					$('.sidebar-elements').sidebar('is hidden', $('.sidebar-elements').sidebar('show')); $('.set_container_basic').removeClass('sortable');
				},
				onHide: function () {
					$('.sidebar-elements').sidebar('hide'); $('.set_container_basic').addClass('sortable');
					$('.sidebar-elements,.sidebar-popup-setting').sidebar('hide');
				},
			})
				.modal('show');
		},
		success: function (data) {
			$('.autopopup>.content').html(data);
		},
		url: "gadgets/autopopup/include.inc.php",
		global: false,
		type: 'POST',
		dataType: 'html',
	});
}

// Macht Button sichtbar oder unsichtbar
function call_edit_modus(mod) {

	if (mod == 'on') {
		$('.layer_button_splitter,.layer_button_textfield,.layer_button,#span_button_edit_head,#row_field').css({ 'visibility': 'visible' });
		$('.edit_modus').show();
		$('#scrollUp').css('visibility', 'visible');
	} else {
		$('.layer_button_splitter,.layer_button_textfield,.layer_button,#span_button_edit_head,#row_field').css({ 'visibility': 'hidden' });
		$('.edit_modus').hide();
		$('#scrollUp').css('visibility', 'hidden');
	}
}


// Nach speichern der Layouts, sollen die Textfelder wieder editierbar sein
function function_close_form() {
	// $( "#smart_content_header_text" ).stop();
	// $('.smart_content_header').backgroundDraggable('disable');
	$('.smart_content_header').unbind();
	$('.smart_content_header').css('cursor', 'unset');
	haeder_hover();

	// $('.smart_content_header, #span_button_edit_head').on({
	// mouseenter:function() {
	// if (Cookies.get("edit_modus") == 'on') { $('#button_edit_head').show(); }
	// },
	// mouseleave:function() {
	// if (Cookies.get("edit_modus") == 'on') { $('#button_edit_head').hide(); }
	// }
	// });

	// save_content();
}

// Holt den Content für die Bearbeitung des Amazon-seiten
function call_sitebar_content(set) {
	if (!set) var set = 'menu';
	$.ajax({
		url: "admin/inc/sidebar_structure.php",
		global: false,
		async: false,
		data: ({ 'set': set }),
		type: 'POST',
		dataType: 'html',
		success: function (data) {
			$('#sidebar-content-structure-' + set).html(data);
			$('.tree_container').height($(window).height() - 180);
		}
	});
}


// Öffent Head
function CallEditHead() {
	$('.sidebar-design').sidebar('is hidden', $('.sidebar-design').sidebar('show'));
	// $('#tabgroup_form_design').find('.item').tab('change tab',
	// 'background');
	$('#accordion-design').accordion('open', 2);
}

// Ruft gesamten Content der Seite auf und setzt alles Js-Parameter zum
// bearbeiten der Felder
function CallContentSite(site_id) {

	$.ajax({
		url: "site_content.php",
		// global :false,
		// async :false,
		data: ({ site_id: site_id, ajax: true }),
		type: "POST",
		dataType: "json",
		beforeSend: function () {

			$('.autopopup').remove(); // Modalwindow for autopopup
			// remove is
			// important
			// $('.ui.toast').hide();
			$('body').toast({
				progressUp: true,
				displayTime: 2000,
				message: 'Seite wird geladen...',
				position: 'top center',
				showProgress: 'top',
			});

			// $('#call-loader').show();
			$('.sidebar-element-setting').sidebar('hide');

		},
		success: function (data) {

			// Ruft ab auf ob Seite favorisiert ist
			CallFavorite();
			$('#get_title').html(data.title);
			$('.get_public_url').attr("href", data.public_url)
			// $('#call-loader').hide();
			$('.hc-offcanvas-nav,.cke_autocomplete_panel').remove();
			// $('#load-smart-content').html(data.add_css2+data.content);
			$('#container_body').replaceWith(data.add_css2 + data.content);
			$('#set_style').html(data.set_style);

			SetSortable();
			SetNewTextfield();

			if (Cookies.get("edit_modus") == 'on') {
				// $('#button_edit_head').show();

				//				$('.cktext').ckeditor();
				//				for (var instance in CKEDITOR.instances) {
				//					CKEDITOR.instances[instance].destroy();
				//				}

				save_content();
			}

			// $('.ui.toast').fadeOut("slow");

			get_padding_hight();
		},
		error: function (xhr, status, thrown) {
			// alert(status);
			// $(location).attr('href','../');
			location.reload(true);

		}
	});
}

// Ruft Inhalt von layer und übergibt diesen
function CallContentLayer(layer_id) {

	$.ajax({
		url: "admin/ajax/layer_new_inc.php",
		// global :false,
		// async :false,
		data: ({ layer_id: layer_id }),
		type: "POST",
		dataType: "html",
		beforeSend: function () {
			$('#sort_' + layer_id).html('<div class="ui basic segment"><br><br><div class="ui active inverted dimmer"><div class="ui text loader">Amazon-Inhalte werden geladen...</div></div><p></p></div>');
		},
		success: function (data) {
			$('#ProzessBarBox').message({ type: 'success', title: 'Layer geladen' });
			$('#sort_' + layer_id).replaceWith(data);
			SetNewTextfield();

		},
		error: function (xhr, status, thrown) {
			alert(status);
		}
	});
}


/*******************************************************************************
 * Layer sortable
 ******************************************************************************/
function SetSortable() {

	//	
	// // Verändert bei mousemove den Hintergrund
	// $('#container_body').hover( function() {
	// if (Cookies.get("edit_modus") == 'on') {
	// $('.show_admin_line').show();
	// }
	// }, function() {
	// if (Cookies.get("edit_modus") == 'on') {
	// $('.show_admin_line').hide();
	// }
	// });
	$('.show_admin_line').hide();

	// Check actual padding
	var header_padding = $('#header').css('padding-top');
	var header_padding_int = parseInt(header_padding);

	$(".sortable").sortable({
		// containment :'#garten',
		// containment : 'window',
		connectWith: ".sortable",
		handle: '.button_sort',
		revert: true,
		helper: getHelper,
		distance: '50',
		// zIndex : '400',
		// placeholder: "portlet-placeholder ui-corner-all",
		placeholder: "portlet-placeholder ui message",
		// revert: '0.5',
		opacity: 0.5,
		// forceHelperSize: true,
		tolerance: 'pointer',
		grid: [20, 20],
		cursor: 'move', // Curvor bei Verschiebung mit "move" Symbol

		start: function (event, ui) {

			if ($(".set_container_basic").hasClass("sortable")) {
				$(ui.helper).addClass("element_helper");
				$('.show_admin_line').show();
				$('.grid_field').addClass("grid_field_hover");

				$('#footer,#header2,#left_0').animate({ 'padding-top': '10px', 'padding-bottom': '10px' }, 300);

				if ($('#header2').height() == '') {
					$('#header2').animate({ 'padding-top': '30px', 'padding-bottom': '30px' }, 300);
				}

				if ($('#footer').height() == '') {
					$('#footer').animate({ 'padding-top': '30px', 'padding-bottom': '30px' }, 300);
				}

				if ($('#left_0').height() == '') {
					$('#left_0').animate({ 'padding-top': '30px', 'padding-bottom': '30px' }, 300);
				}
			}
		},
		stop: function (even, ui) {
			$('.show_admin_line').hide();
			$('.grid_field').removeClass("grid_field_hover");
			// $('.set_container_basic').removeClass('sortable_field',
			// {duration:200});
			// Ladet die style neu
			$('#left_0,#header2,#footer').animate({ 'padding-top': '0px', 'padding-bottom': '0px' }, 300);
		},
		update: function (event, ui) {

			$('.dropdown').dropdown('hide');
			// $('.tooltip,.edit_tooltip').tooltipster();
			if (ui.item.hasClass("new_module")) {
				var module_id = $(".move_draggable").attr('id');
				// Speichert das Textfeld oder das Modul
				$.ajax({
					url: "admin/ajax/layer_new_inc.php",
					global: false,
					async: false, // muss bestehen bleiben damit
					// die
					// Parameter nach success übergeben
					// werden (new_textfield)
					data: ({ module_id: module_id }),
					type: "POST",
					dataType: "html",
					beforeSend: function () {
						ui.item.html('<div class="ui basic segment"><br><br><div class="ui active inverted dimmer"><div class="ui text loader">Bitte warten...</div></div><p></p></div>');
					},
					success: function (data) {
						ui.item.html(data);
						SetNewTextfield();

					}
				});

				$('.new_module').removeClass('move_draggable');
				// auslesen der ID zum hinzufuegen in die
				// Sortable-liste
				var array_textfield_id = ($(".new_textfield").attr('id'));

				// loescht die Klasse fuer das anlegen weiterer
				// Felder
				$('.textfield_div,.splitter_div').removeClass('new_textfield');
				// function zum hinzufuegen in die
				// Sortable
				// Liste
				var _this = this;
				$(this).find("div").each(function (i) {
					if (this == ui.item[0]) {
						var strId = $("div:eq(" + ((i == 0) ? 1 : 0) + ")", _this).attr("id");
						// alert(strId);
						var ID_array = array_textfield_id.split("_");
						id = ID_array[1];
						$(this).attr('id', strId.substr(0, strId.lastIndexOf("_")) + "_" + id);
					}
				});

				// AUFRUF wenn es sich um KEIN Textfeld oder
				// Amazon-Tool handelt
				// - wird setting nicht aufgerufen
				if (module_id != 'textfield' && module_id != 'amazon') {
					// Es wird direkt der Explorer geladen
					if (module_id == 'photo') showDialog(id, 'photo');
					else call_element_setting(id, module_id);
				}
			}

			$('.tooltip').popup();
			$('.tooltip-left').popup({ position: "left center" });
			$('.tooltip-top').popup({ position: 'top center' });
			$('.tooltip-right').popup({ position: 'right center' });

			// Für das richtige Darstellen bei FlexImages
			$('.flex-images').flexImages({ rowHeight: 140 });
			// Sortierung auslesen und in der Datenbank speichern
			serial = $('#' + this.id).sortable('serialize');
			id_position = this.id;
			$.ajax({ url: 'admin/ajax/layer_sort_save.php?id_position=' + id_position, data: serial, type: "post", dataType: 'script' });
		}
	});

}

function getHelper(event, ui) {
	var helper = $(ui).clone();
	helper.addClass("ui message element_helper");
	return helper;
}

/*******************************************************************************
 * Function: Neuen Textfeld anlegen und bearbeitbar machen
 ******************************************************************************/
function SetNewTextfield() {

	$('.layer_button_dropdown').dropdown();

	$('.tooltip').popup();
	$('.tooltip-left').popup({ position: "left center" });
	$('.tooltip-top').popup({ position: 'top center' });
	$('.tooltip-right').popup({ position: 'right center' });

	// Verändert bei mousemove den Hintergrund
	$('.splitter_div').on({

		mouseenter: function () {
			if (Cookies.get("edit_modus") == 'on' && $('.sidebar-design').sidebar('is hidden') == true) {
				$('.layer_button_splitter').hide();
				$('.splitter_div').css({ 'border': '' });
				var value = 'sort_' + $('#update_id').val();

				if (value == this.id && $('.sidebar-element-setting').sidebar('is open')) {
					$('#' + value + '.splitter_div').addClass('hover_box_red', { duration: 300 });
				} else {
					$('#' + this.id + '.splitter_div').addClass('hover_box_orange', { duration: 300 });
				}

				$('#' + this.id + '.splitter_div > div > .layer_button_splitter').show();
			}
		},
		mouseleave: function () {
			if (Cookies.get("edit_modus") == 'on') {
				$('.splitter_div').removeClass('hover_box_blue hover_box_red hover_box_orange')
				var value = 'sort_' + $('#update_id').val();
				if ($('.sidebar-element-setting').sidebar('is open')) {
					$('#' + value + '.splitter_div').addClass('hover_box_red', { duration: 500 });
				}

				$('.layer_button_splitter').hide()
			}
		}
	});


	$('.textfield_div').on({
		mouseenter: function () {
			if (Cookies.get("edit_modus") == 'on' && $('.sidebar-design').sidebar('is hidden') == true) {
				$('.layer_button_textfield').hide();
				$('.admin-formular-fieldbar').hide();
				// $('.textfield_div').css( {'border' :'thin
				// solid
				// transparent'});
				var value = 'sort_' + $('#update_id').val();
				if (value == this.id && $('.sidebar-element-setting').sidebar('is open')) {
					$('#' + value + '.textfield_div').addClass('hover_box_red');
				}
				else
					$('#' + this.id + '.textfield_div').addClass('hover_box_blue');

				$('#' + this.id + '.textfield_div > div > .layer_button_textfield').show();
			}

		},
		mouseleave: function () {
			if (Cookies.get("edit_modus") == 'on') {
				$('.textfield_div').removeClass('hover_box_blue hover_box_red hover_box_orange');
				var value = 'sort_' + $('#update_id').val();
				if ($('.sidebar-element-setting').sidebar('is open')) {
					$('#' + value + '.textfield_div').addClass('hover_box_red')
				}

				// $('.textfield_div').css( {'border' :'thin
				// solid
				// transparent'});
				$('.layer_button_textfield').hide();
			}
		}
	});

	// Button_leiste im Content verstecken
	$('.admin-formular-fieldbar,.layer_button_splitter,.layer_button_textfield').hide();
	$('.layer_button_textfield,.layer_button_splitter').css('visibility', 'visible');
	// $( ".sortable" ).sortable( "refresh" );
}


/*******************************************************************************
 * CKEDITOR - SAVE FUNCTION
 ******************************************************************************/
function save_content_id(content_id) {
	// http://docs.ckeditor.com/#!/api/CKEDITOR.editor-event-change
	CKEDITOR.inline(content_id, {
		on: {
			focus: function (event) {
				call_edit_modus('off');
				$('body').addClass('ckeditor'); // if(!$('body').hasClass('ckeditor'))
				// { }
			},
			blur: function (event) {
				var data = event.editor.getData();
				fu_save_content(content_id, data);
				call_edit_modus('on');
				$('body').removeClass('ckeditor');
			},
			instanceReady: function (event) {
				// Autosave but no more frequent than 5 sec.
				var buffer = CKEDITOR.tools.eventsBuffer(5000, function () {
					var data = event.editor.getData();
					// Nur aufrufen wenn Editor noch offen ist
					if (Cookies.get("edit_modus") == 'on') { fu_save_content(content_id, data); }
				});
				this.on('change', buffer.input);
			}
		}
	});
}

function fu_save_content(content_id, data) {
	var request = jQuery.ajax({
		url: 'admin/ajax/content_save.php',
		type: "POST",
		data: {
			content: data,
			content_id: content_id
		},
		success: function (data) {
			if (data == 'ok') { $('#short_info_box').stop(true, true).html("<div data-position='bottom center' data-tooltip='Automatische Speicherung'><i class='icon  circular small inverted green save'></i></div>").fadeIn(1000).fadeOut(3000); }
			else { alert('Fehler beim speichern aufgetreten'); }
		},
		dataType: "html"
	});
}

function save_content() {
	/* Automatisches Speichern nach Bearbeitung */
	$("div[contenteditable='true']").each(function (index) {
		var content_id = $(this).attr('id');
		// var tpl = $(this).attr('tpl');
		save_content_id(content_id);
	});
}


/*******************************************************************************
 * Dialog - NEW Semantic
 ******************************************************************************/
function semantic_dialog(url, size) {
	if (!size) size = 'large';
	$.ajax({
		beforeSend: function () {
			$("#modal_content").html('<div class="ui active inverted dimmer"><div class="ui text loader">Inhalt wird geladen</div></div>');
			$('.' + size + '.ui.modal.basic').modal('show');
		},
		success: function (data) {
			$("#modal_content").html(data);
		},
		url: url,
		global: false,
		type: "POST",
		dataType: "html",
	});
}

function function_edit_modus_on() {
	$('.smart_edit_modus').val('On');
	$('.smart_edit_modus').attr('id', 'on');
	$('#button_lock').attr('class', "icon grey large unlock");
	$("div[contenteditable='false']").attr('contenteditable', 'true');
	$('.edit_modus').show();
}

function function_edit_modus_off() {

	$('.smart_edit_modus').val('Off');
	$('.smart_edit_modus').attr('id', 'off');
	$('#button_lock').attr('class', "icon grey large lock");
	$("div[contenteditable='true']").attr('contenteditable', 'false');
	$('.edit_modus').hide();
}

function set_edit_modus(set) {

	if (set == 'on') {

		Cookies.set("edit_modus", 'off', { expires: 7, path: '' });
		function_edit_modus_off();
		for (var instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].destroy();
		}
	}
	else {
		Cookies.set("edit_modus", 'on', { expires: 7, path: '' });
		function_edit_modus_on();
		save_content();
		// CKEDITOR.inlineAll()
	}
}

// Ruft Status ob Seite favorisiert ist oder nicht
// Optional kann der Statuts true übergeben dann wird favo status getoggelt
function CallFavorite(toggle) {

	$.ajax({
		url: "admin/ajax/set_favorite.php",
		global: false,
		async: false,
		type: 'POST',
		data: { 'toggle': toggle },
		dataType: 'script'
	});

	// Reload Dropdown
	$.ajax({
		url: "admin/ajax/call_dropdown_search_sites.php",
		global: false,
		async: false,
		type: 'POST',
		dataType: 'html',
		success: function (data) { $('#dropdown_search_sites').replaceWith(data); $('.dropdown').dropdown({ on: 'hover', fullTextSearch: true }); }
	});
}


function addNewSite() {
	$.ajax({
		url: "admin/ajax/form_edit.php",
		global: false,
		async: false,
		type: 'POST',
		dataType: 'html',
		data: { 'list_id': 'site_list' },
		success: function (data) {
			$('#option_site>.content').html(data);
			$('#option_site').modal('show');
		}
	});
}

function cloneSite(clone_id) {
	$.ajax({
		url: "admin/ajax/form_edit.php",
		global: false,
		async: false,
		type: 'POST',
		dataType: 'html',
		data: { 'list_id': 'site_list', 'clone_id': clone_id },
		success: function (data) {
			$('#option_site>.content').html(data);
			$('#option_site').modal('show');
		}
	});
}

function editMenuStructure() {
	$.ajax({
		url: "admin/ajax/edit_menu.php",
		global: false,
		async: false,
		type: 'POST',
		dataType: 'html',
		success: function (data) {

			$('.modal-edit-menu>.content').html(data);
			$('.modal-edit-menu').modal({ allowMultiple: true, observeChanges: true });
			$(".modal-edit-menu").modal('setting', 'can fit', true);
			$('.modal-edit-menu').modal('show');
		}
	});
}


function haeder_hover() {

	$('#button_edit_head').hide();
	// Verändert bei mousemove den Hintergrund
	$('.smart_content_header').hover(function () {
		if (Cookies.get("edit_modus") == 'on') {
			// $('.smart_content_header').addClass("header_hover");
			$('#button_edit_head').show();
		}
	}, function () {
		if (Cookies.get("edit_modus") == 'on') {
			// $('.smart_content_header').removeClass("header_hover");
			$('#button_edit_head').hide();
		}
	});
}

function set_ui_modal() {

	$('.ui.modal').modal({

		allowMultiple: true,
		observeChanges: true,
		// centered: false,
		autofocus: false,
		closable: false,
		'can fit': true,
		onShow: function () {
			call_edit_modus('off');
			$('.tooltip').popup();
			$('.tooltip-left').popup({ position: "left center" });
			$('.tooltip-top').popup({ position: 'top center' });
			$('.tooltip-right').popup({ position: 'right center' });
		},
		onHidden: function () {
			call_edit_modus('on');
		}
	});
}


// SetNewTextfield ();
