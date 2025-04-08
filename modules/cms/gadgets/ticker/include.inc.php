<?
/************************************************************************************
 * LIVE TICKER
 ************************************************************************************/
$set_ticker = $GLOBALS['set_ticker'] ++;

if (! $set_ticker) {
	$add_js2 .= "
			var i ;
	function Interval(layer_id,time) {
		setInterval(function() {
			$.ajax( {
				url :'gadgets/ticker/call_content.php',
				data : { layer_id : layer_id},
				global :false,
				type :'POST',
				dataType :'html',
				success : function (data){
					$('#ticker'+layer_id).html(data);
				}
			})
		}, time);
	}";
}

$add_js2 .= "if (!i) var i = new Interval($layer_id,5000);";

$output .= "<span id='ticker$layer_id'>Ticker wird geladen...</span>";