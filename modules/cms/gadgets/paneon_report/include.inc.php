<?php
$output .= "<div id='report'></div>";

if ($_SESSION ['admin_modus']) {
	$report_id = $_GET ['id'];

	if ($report_id)
		$set_container = 'left_0';
	else
		$set_container = 'report';

	$add_js2 .= "
	$(document) .
			ready ( function () {
		
			$.ajax({
				url : 'gadgets/paneon_report/report.php',
				global : false,
				type : 'POST',
				data : ({'report_id': '$report_id' }),
				dataType : 'html',
				beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
				success : function(data) { $('#$set_container') . html ( data );
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
			url : 'gadgets/paneon_report/report.php',
			global : false,
			type : 'POST',
			data : ({'report_id': report_id }),
			dataType : 'html',
			beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
			success : function(data) { $('#report').html(data); }
		});

	});
";
	return;
}

