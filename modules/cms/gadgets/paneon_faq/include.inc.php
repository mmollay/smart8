<?php
$output .= "<div id='faq'></div>";

if ($_SESSION ['admin_modus']) {
	$faq_id = $_GET ['id'];

	if ($faq_id)
		$set_container = 'left_0';
	else
		$set_container = 'faq';

	$add_js2 .= "
	$(document) .
			ready ( function () {
		
			$.ajax({
				url : 'gadgets/paneon_faq/faq.php',
				global : false,
				type : 'POST',
				data : ({'faq_id': '$faq_id' }),
				dataType : 'html',
				beforeSend : function() {  $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
				success : function(data) { $('#$set_container') . html ( data );
				}
			} );
		});
";
} else {

	//Ruft über Ajax das Eingabefeld
	//!!!DONT Change the Parameter ": faq_id" will be relpaced when the system generate the page "page_generate.php"
	$add_js2 .= "
	$(document).ready(function() {
		$add_value
		$.ajax({
			url : 'gadgets/paneon_faq/faq.php',
			global : false,
			type : 'POST',
			data : ({'faq_id': faq_id }),
			dataType : 'html',
			beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
			success : function(data) { $('#faq').html(data); }
		});

	});
";
	return;
}

