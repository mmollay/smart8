<?php
$output .= "<div id='learning_content'></div>";

$add_js2 .= "
	$(document).ready ( function () {
		    $.ajax({
				url : 'gadgets/learning/question_field.php',
				global : false,
				type : 'POST',
				data : ({ 'layer_id' : '$layer_id' }),
				dataType : 'html',
				beforeSend : function() { $('#learning_content').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
				success : function(data) { $('#learning_content').html(data); }
		    });
	});
";