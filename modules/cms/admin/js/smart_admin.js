$(document).ready( function () {
	

	if (window.IsDuplicate()) {
	    // alert user the tab is duplicate
		$(location).attr('href','../pages/error.php?duplicate=true');

	    // close the current tab
	   // window.close();
	}
	
	// Stellt in der Struktur und in Sitbars die Höhe zu scrollen ein
	$(window).resize(function() {
	    $('.tree_container').height($(window).height() - 140);
	    $('.sitebar_container').height($(window).height());
	});

	$(window).trigger('resize');

	
// ClassicEditor.create( document.querySelector( '.editor5' ) )
// .catch( error => {
// console.error( error );
// } )
	
	$('#button_layout_sidebar').click ( function () { $('.sidebar-design').sidebar('toggle'); });
	
// $('#button_element_sidebar').click ( function () {
// $('.sidebar-elements').sidebar('toggle');
// });
	
	$('#upload_hole_page').checkbox();	
	
	$('.button-set-public').popup({
	    popup : $('.tooltip-set-public'),
	    hoverable  : true,
	   // on : 'click',
	    delay: {
	        show: 100,
	        hide: 1000
	    }
	});
	
	
	$('.button-elements').popup({
	    popup : $('.tooltip-elements'),
	    	inline     : true,
	    hoverable  : true,
	    delay: {
	        show: 300,
	        hide: 800
	    }
	});

 	$('.sidebar-elements').sidebar({  dimPage : false,  transition: 'overlay' });
 	$('.sidebar-popup-setting').sidebar({closable : false, dimPage : false,  transition: 'overlay'});
 	
	$('.sidebar-element-setting').sidebar({closable : false, dimPage : false,  transition: 'overlay', // closable
		onVisible: function() {
			// call_edit_modus('off');
			var value = 'sort_'+$('#update_id').val();
			$('#' + value + '.textfield_div').addClass('hover_box_green');
			$('#' + value + '.splitter_div').addClass('hover_box_green');
		},
		onHide: function() {
			// call_edit_modus('on');
			$('.textfield_div,.splitter_div').removeClass('hover_box_green');
		}
	})
	
	$('.sidebar-design').sidebar({ closable : false, dimPage : false,  transition: 'overlay', 
		onVisible: function() { 
			call_form_design();
			call_edit_modus('off');
// for (var instance in CKEDITOR.instances) {
// CKEDITOR.instances[instance].destroy();
// }
		},
		onHide: function() { 
			function_close_form();
			call_edit_modus('on');
		}
	})
	
	$('.sidebar-menu').sidebar({ closable : false, dimPage : false,  transition: 'overlay', 
		onVisible: function() { call_sitebar_content('');},
		onHide: function() { }
	})
	
	$('.sidebar-funnel').sidebar({ closable : false, dimPage : false,  transition: 'overlay', 
		onVisible: function() { call_sitebar_content('funnel');},
		onHide: function() { }
	})
	
	
	// $('.sidebar-top').sidebar({ closable : false, dimPage : false,
	// transition: 'overlay'}).sidebar('toggle');
	// $('.sidebar-right').sidebar({ closable : false, dimPage : false,
	// transition: 'overlay'}).sidebar('toggle');
	

	// $('.sidebar-elements').sidebar('toggle');
	// $('.sidebar-elements-menu').dropdown();
	
// $('#view_phone_size').click( function(){
// $('body').previewer('show');
// });
	
	$('#view_phone_size').click( function(){		
		var company = $('.company').attr('id');
		var site_id = $('.site_id').attr('id');
		$('.modal_phone_version').modal('show');
		$('.preview_phone_frame').attr('src','../ssi_smart/index.php?site_id="+site_id+"&preview=true&company="+company+"');
	});
	
	$('body').removeClass('pushable');
	
	// $('html,body').css({'overflow': 'auto'}); //!!!mit dieser Einstellung
	// geht der Parallax-effect nicht mehr
	
	$('.tooltip').popup();
	$('.tooltip-left').popup({ position:"left center" });
	$('.tooltip-top').popup({ position: 'top center'});
	$('.tooltip-right').popup({ position: 'right center'});
	
	
	// $('html, body').css({'overflow': 'hidden','height': '100%' });
	// $('#page-loading').html("<div class='ui active inverted dimmer'><div
	// class='ui medium text loader'>Seite wird geladen!</div></div>");
	window.onload = function () { 
		// $('#page-loading').fadeOut("");
		
		// $('.load-flex-images').hide();
		// $('.flex-images').show();
		// $('.flex-images').flexImages({rowHeight: 140});
		$('#call-loader').hide();
		$('html').css("background", "");
	};
	
	// Wenn der Editmodus nicht definiert ist, standarmassig ON
	if (!Cookies.get("edit_modus")) {
		Cookies.set("edit_modus", 'on', { expires: 7, path: '' });
	}
	
	// $('.dropdown_menu').dropdown({ on: 'hover'}); //deaktiviert weil es
	// sonst
	// ein em_gemeinschaft die Gruppen-Filterung blockieren würde
	// $('#dropdown_menu, .dropdown_menu').dropdown({ on: 'hover'});
	$('.dropdown').dropdown({ on: 'hover', fullTextSearch : true });
	
	haeder_hover();
	
	// Anzeigefeld ob Inhalte bei Content speichert wurden
	$('#short_info_box').hide();
	
	// Reload der Seite
	// $('#button_reload').click( function() { $(location).attr('href', '');
	// return false; });
	$('.button_reload').click( function() { CallContentSite($('.site_id').attr('id')) });
	
	// $('#button_add_layer').click( function(e) { e.preventDefault();
	// CallDialog("Neuen Layer
	// anlegen","admin/ajax/form_layer.php",'','form','400','200',true); });
	
	CallFavorite();
	
	$('#call_links').click( function() {
		$.ajax( {
			url      : "admin/links_info.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('#modal_call_links>.content').html(data);
				$('#modal_call_links').modal('show');
			}	
		});
	});
	
	$('#button_versiontext').click( function() {
		$.ajax( {
			url      : "admin/version_info.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('.modal_version>.content').html(data);
				$('.modal_version').modal('show');
			}	
		});
	});
	

	// Modal für Popup-Moadal
	$('.button_auto_popup').click( function () {
		open_autopopup_admin();
	});
	
	
	$('#button_set_public').click( function(){ 
	    
	    	var responseLen = 0;
		// Gruppen auslesen
		$.ajax( {
			beforeSend : function() { 
				$("#modal_window").html(`
				<div class="ui basic segment"><br>
				<div class="ui active inverted dimmer"><div class="ui text loader"></div><i class="cloud huge green upload alternate icon"></i></div></div>
				<div class='container_show_data_page_generator' align=center><b>Status:</b><div id="show_data_page_generator"></div></div>
				`); 
				$('.small.ui.modal.load').modal({ closable : false}).modal('show');
				$('.cancel.button.load').addClass('disabled');
			},	
			success:    function(data){ 
				$('.cancel.button.load').removeClass('disabled');
				$('#container_show_data_page_generator').html('');
				
				if (data.search("successful_generate") != -1) { 
				    var public_url = $('.get_public_url').attr('href');
				    $("#modal_window").html(`<div align=center>Gratuliere deine neue Webseite ist online! <em data-emoji="slight_smile" class="link"></em><br><br><i class="ui big green icon checkmark"></i><br><br><a class="ui icon circular button green small" target="new" href="`+public_url+`"><i class="icon desktop"></i>&nbsp; Öffentliche Webseite aufrufen</a></div>`); 
				} else {
				    $('#modal_window').html("<div align=center>Fehler beim erzeugen der Seite <i class='ui large red icon warning sign'></i><br><br>Meldung:<br>"+data+"</div>");
				}
			},				
			url :"admin/ajax/page_generate.php",
			global   : false,
			// async : false,
			data : ({'upload_hole_page': $('#checkbox_upload_hole_page').is(':checked') }),
			type     : "POST",	
			dataType : "html",
			xhr: function(){
				var xhr = $.ajaxSettings.xhr();
				xhr.onprogress = function(e){
					data = e.currentTarget.responseText.substr(responseLen);
					responseLen = e.currentTarget.responseText.length;
					$('#show_data_page_generator').html(data);	
				};
				return xhr;
			},
		});
	})
	
	
	
// $('#button_set_public').click( function(){
// // Gruppen auslesen
// $.ajax( {
// beforeSend : function() {
// $("#modal_window").html(`<div class="ui basic segment"><br><br><div class="ui
// active inverted dimmer"><div class="ui text loader">Einstellungen werden
// geladen...</div></div><p></p></div>`);
// },
// success: function(data){
// $("#modal_window").html(data);
// },
// url :"admin/ajax/form_page_generator.php",
// global : false,
// // async : false,
// type : "POST",
// dataType : "html",
// });
// })
	
	
	/***********************************************************************
	 * Laden der Seitenübersicht
	 **********************************************************************/
	$('#button_allsites').click( function() {
		$.ajax( {
			url      : "admin/ajax/list_sites.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('.allsites>.content').html(data);
				set_ui_modal();
				$('.allsites').modal('show');
			}	
		});
	});
	
	/***********************************************************************
	 * Laden der Archivliste
	 **********************************************************************/
	$('#button_archive').click( function() {
		$.ajax( {
			url      : "admin/ajax/list_archive.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('.list_archive>.content').html(data);
				set_ui_modal();
				$('.list_archive').modal('show');
			}	
		});
	});
	
	/***********************************************************************
	 * Vorlage erzeugen
	 **********************************************************************/
	$('#button_template').click( function() {
		
		$.ajax( {
			url      : "admin/ajax/form_template.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('.form-template>.content').html(data);
				set_ui_modal();
				$('.form-template').modal('show');
			}	
		});
	});
	
	/***********************************************************************
	 * Einstellungen einzelner Seiten
	 **********************************************************************/
	$('#button_option_site').click( function() {
		var site_id = $('.site_id').attr('id');
		$.ajax( {
			url      : "admin/ajax/form_edit.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			data     : { 'list_id':'site_form', 'update_id':site_id },
			success  : function(data) {
				$('#option_site>.content').html(data);
				set_ui_modal();
				$('#option_site').modal('show');
			}	
		});
	});
	
	/***********************************************************************
	 * Allgemeine Einstellungen
	 **********************************************************************/
	$('.button_option_page').click( function() {
		
		$.ajax( {
			url      : "admin/ajax/form_edit.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			data     : { 'list_id':'global_option'},
			success  : function(data) {
				$('#option_global>.content').html(data);
				set_ui_modal();
				$('#option_global').flyout('show');
			}	
		});
	});
	
	$('#button_insert_share_site').click( function() {
		$.ajax( {
			url      : "admin/ajax/form_share_site.php",
			global   : false,
			async    : false,
			type     : 'POST',
			dataType : 'html',
			success  : function(data) {
				$('#option_global>.content').html(data);
				set_ui_modal();
				$('#option_global').modal('show');
			}	
		});
	});

	if (Cookies.get("edit_modus") == 'on') {
		function_edit_modus_on()
	}
	else {
		function_edit_modus_off() 
	}
	
	$('.smart_edit_modus').click( function() {
		set_edit_modus(this.id);		
	});	

	if (!$('#index_id').val()) {
		$('.button_option_page').click();
	}

	// The "instanceCreated" event is fired for every editor instance
	// created.
	CKEDITOR.on( 'instanceCreated', function( event ) {
		var editor = event.editor, element = editor.element;
		editor.config.filebrowserBrowseUrl = 'admin/ckeditor_link.php?type=Images';
	});
	
	save_content();

})

$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
}


// function set_ui_modal() {
// $('.ui.modal').modal({
// allowMultiple: true,
// observeChanges : true,
// // centered: false,
// autofocus : false,
// closable : false,
// 'can fit' : true,
// onShow : function(){
// call_edit_modus('off');
// $('.tooltip').popup();
// $('.tooltip-left').popup({ position:"left center" });
// $('.tooltip-top').popup({ position: 'top center'});
// $('.tooltip-right').popup({ position: 'right center'});
// },
// onHidden : function() {
// call_edit_modus('on');
// }
// });
// }


// Aufruf, des Designers für die Darstellung der Elemente
// ///// WAR ein Test ob Seitenoptionen auch in der Sidebar sein soll - ist aber
// nicht gut, sehr viel Text in den Optiopnen verwendet wird
// function call_option(site_id) {
//	
// $.ajax( {
// beforeSend: function () {
// //$('#call-loader').show()
// },
// success: function(data){
// $("#sidebar-option-content").html(data);
// $('.sidebar-design,.sidebar-elements').sidebar('hide');
// $('.sidebar-option').sidebar('show');
// },
// url :"admin/ajax/form_edit.php",
// global : false,
// data : { 'list_id':'site_form', 'update_id':site_id },
// type : "POST",
// dataType : "html",
// });
// }
