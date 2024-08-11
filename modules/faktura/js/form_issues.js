$(document).ready(function () {

	$('#date_create').change(function () {
		if ($('#date_booking').val() == '') $('#date_booking').val($('#date_create').val());
		$('#description').focus();
	});

	$('.ui.search').search({
		apiSettings: { url: 'inc/search_list.php?q={query}' },
		minCharacters: 2,
		onSelect: function (result, response) {
			$.ajax({
				url: "inc/search_account.php",
				global: false,
				type: "POST",
				data: ({ bill_id: result['bill_id'] }),
				success: function (id) {
					$("#dropdown_account").dropdown('set selected', id);

				},
				dataType: "script"
			});
		}
	});

	$('#description').bind('keyup change', function () {

		if (!$("#description").val()) {
			$("#dropdown_account,#dropdown_client_id").dropdown('clear');
			$('#brutto,#netto,#comment').val('');
		}

		if ($("#description").val().length >= 5 && !$("#dropdown_account").dropdown('get value')) {
			$.ajax({
				url: "inc/search_account.php",
				global: false,
				type: "POST",
				data: ({ search_text: $("#description").val() }),
				success: function (id) {
					$("#dropdown_account").dropdown('set selected', id);
				},
				dataType: "script"
			});

		}
	});

	$("#input_group").on("keypress", function (event) {
		if (event.which == 13 && !event.shiftKey) {

			event.preventDefault();

			$.ajax({
				url: "inc/add_new_issues_group.php",
				type: "POST",
				data: ({ name: $('#input_group').val() }),
				dataType: "html",
				success: function (data) {
					if (data == 'exist') {
						$('#message').message({ status: 'error', title: 'Gruppe "' + $('#input_group').val() + '" existiert bereits' });
					}
					else {
						$('#dropdown_client_id .menu').append('<div class="item" data-value="' + data + '" >' + $('#input_group').val() + '</div>');
						$('#dropdown_client_id').dropdown('refresh');
						$('#dropdown_client_id').dropdown('set selected', data);
						$('#add_group').popup('hide');
					}
				}

			});
		}
	});

	$('#add_group')
		.popup({
			popup: $('.group.popup'),
			on: 'click',
			onVisible: function () {
				$('#input_group').focus();
			},
			onHide: function () {
				$('#input_group').val('');
			}

		});

	$('#brutto').bind('keyup change', function () {
		if ($('#brutto').val() != '') { $('#netto').prop('disabled', true); $('#netto').val(''); }
		else { $('#netto').prop('disabled', false); }
	});

	$('#netto').bind('keyup change', function () {
		if ($('#netto').val() != '') { $('#brutto').prop('disabled', true); $('#brutto').val(''); }
		else { $('#brutto').prop('disabled', false); }
	});

	/*
	 * call Accound after write company_1
	 */
	$('#company_1,#description').bind('keypress', function (e) {
		if ($('#account').val() == '' && (e.keyCode == 9 || e.keyCode == 13) && ($('#company_1').val() || $('#description,').val())) { //tab
			$.ajax({
				url: "inc/call_account_id.php",
				global: false,
				type: "POST",
				data: ({ search_text1: $('#company_1').val(), search_text2: $('#description').val() }),
				beforeSend: function () { },
				success: function (id) { $("#account").val(id); },
				dataType: "html"
			});
		}
	});

});