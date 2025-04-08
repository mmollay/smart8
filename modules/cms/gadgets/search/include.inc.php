<?php
$output .= "<div id=call_events></div>";

//Ruft über Ajax das Eingabefeld
$add_js2 .= "
	$(document).ready(function() {
		$.ajax( {
			url :'gadgets/search/get_results.php',
			global :false,
			type :'POST',
			data :( {ajax : true }),
			dataType :'html',
		    beforeSend : function() { $('#call_events').html('<br><br><br><div class=\"ui active inverted dimmer\"><div class=\"ui loader\"></div></div><p></p>'); },
			success: function(data) { $('#call_events').html(data);  }
		});
	});
";

return;
// $output = "		
// 	<div align=center>
// 	<form method='post'action='get_results.php' onsubmit='return do_search ();'>
// 		<div class='ui action input'>
// 			<input type='text' id='search_term' name='search_term' placeholder='Suchtexteingabe' onkeyup='do_search ();'>
// 			<button class='ui button' name='search'><i class='search icon'></i> Suchen</button>
// 		</div>
// 	</form>
// 	</div>
// 	<div id='result_div'></div>
// ";

// $add_js2 .= "
// function do_search()
// {
//  var search_term=$('#search_term').val();
//  $.ajax
//  ({
//   type:'post',
//   url:'gadgets/search/get_results.php',
//   data:{
//    search:'search',
//    search_term:search_term
//   },
//   success:function(response) 
//   {
//    document.getElementById('result_div').innerHTML=response;
//   }
//  });
 
//  return false;
// }";
