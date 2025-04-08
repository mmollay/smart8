<?php
$output .= "<div id='challenge_output'></div>";

if ($_SESSION ['admin_modus']) {

	$add_js2 .= "
	$(document) .
			ready ( function () {
		
			$.ajax({
				url : 'gadgets/days21v2/challenges.php',
				global : false,
				type : 'POST',
				data : ({'report_id': '$report_id' }),
				dataType : 'html',
				beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
				success : function(data) { $('#challenge_output') . html ( data );
				}
			} );

		});
";
} else {

	//Ruft über Ajax das Eingabefeld
	//!!!DONT Change the Parameter ": report_id" will be relpaced when the system generate the page "page_generate.php"
	$add_js2 .= "
	$(document).ready(function() {
		$add_value
		$.ajax({
			url : 'gadgets/day21sv2/challenges.php',
			global : false,
			type : 'POST',
			data : ({'report_id': report_id }),
			dataType : 'html',
			beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
			success : function(data) { $('#challenge_output').html(data); }
		});

	});
";
	return;
}
