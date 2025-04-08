//Setting for Popup-Window
function call_popup_setting() {
	$.ajax({
				beforeSend : function() {
					// $('#call-loader').show()
					$('.sidebar-popup-setting').sidebar('show');
					$("#sidebar-popup-setting-content")
							.html(
									'<div class="ui segment"><br><div class="ui active inverted dimmer"><div class="ui active slow double text blue loader"><br>Einstellungen laden</div></div><br><br><br></div>');
				},
				success : function(data) {

					setTimeout(function() {
						$("#sidebar-popup-setting-content").html(data);
						$('.sidebar-design').sidebar('hide'); // ,.sidebar-elements
					}, 500);
				},
				url : "admin/ajax/form_popup_setting.php",
				global : false,
				data : ({
					update_id : $('.site_id').attr('id')
				}),
				type : "POST",
				dataType : "html"
			});
}

// Setting Sitebar from Popup-Window
function load_autosave_popup(site_id) {

	$('.ui-slider-value.form_autopopup').bind("DOMSubtreeModified", function() {
		if ($('#' + this.id).html())
			save_value_autopopup(this.id, $('#' + this.id).html(), site_id);
	});

	// need for checkbox-autosave
	$('.ui-checkbox.form_autopopup').change(function() {
		if ($('#' + this.id + ':checked').val()) {
			save_value_autopopup(this.id, 1, site_id);
		} else {
			save_value_autopopup(this.id, 0, site_id);
		}
	});

	// exlude checkbox
	$('.form_autopopup:not(.no_auto_save,.ui-checkbox)').bind('focus change',
			function() {
				id = this.id;
				if (id) {
					value = $('#' + id).val();
					save_value_autopopup(id, value, site_id);
				}
			});
}

function save_value_autopopup(id, value, site_id) {
	if (xhr != null) {
		xhr.abort();
		xhr = null;
	}

	xhr = $.ajax({
		url : 'admin/ajax/form_popup_setting_autosave.php',
		data : ({
			'id' : id,
			'value' : value,
			'site_id' : site_id,
		}),
		type : 'POST',
		dataType : 'html',
		success : function(data) {
			if (data == 'ok') {
				$('#save_icon').stop(true, true).show().fadeOut(2000);
				// Change Modal-Size
				if (id == 'popup_modal_scrolling') {
					if (value == true)
						$('.autopopup_content').addClass('scrolling');
					else {
						$('.autopopup_content').removeClass('scrolling');
					}
				} else if (id == 'popup_modal_size') {
					$('.autopopup').removeClass('mini tiny small large');
					$('.autopopup').addClass(value);

				} else if (id == 'popup_modal_inverted') {
					if (value == true)
						$('.autopopup').addClass('inverted');
					else {
						$('.autopopup').removeClass('inverted');
					}
				}

			} else
				alert('Fehler beim speichern:' + data)
		}
	});
}