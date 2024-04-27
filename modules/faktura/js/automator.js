if ($('.tr_listvalue').html()) {
	$('#automator_list').show();
}



function insert_inner_issues(automator_id, elba_id = false, fromlist ) {

	// call ajax
	$.ajax({
		url: "elba/insert_inner_issues.php",
		global: false,
		type: "POST",
		data: ({
			elba_id: elba_id,
			automator_id: automator_id,
			fromlist : fromlist
		}),
		dataType: "script",
		success: function() {

			if (!$('.tr_listvalue').html()) {
				$('#automator_list').hide();
			}
			else {
				$('#automator_list').show();
			}

		}
	});
}

function remove_inner_elba(elba_id) {

	// call ajax
	$.ajax({
		url: "elba/delete_from_elba.php",
		global: false,
		type: "POST",
		data: ({
			elba_id: elba_id
		}),
		dataType: "script",
		success: function() {
			if (!$('.tr_listvalue').html()) {
				$('#automator_list').hide();
			}
			else {
				$('#automator_list').show();
			}
		}
	});
}



// call booking
function call_booking_earning(bill_id, elba_id) {

	$.ajax({
		url: "elba/call_booking_earning.php",
		global: false,
		type: "POST",
		data: ({
			bill_id: bill_id,
			elba_id: elba_id
		}),
		dataType: "script"
	});
}

