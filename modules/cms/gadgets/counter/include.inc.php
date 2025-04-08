<?
/************************************************************************************
 * COUNTDOWN
 ************************************************************************************/

if (! $date) {
	$output .= "<div class='message info ui'>Countdown noch nicht definiert</div>";
	return;
}

if (! $GLOBALS['set_counter']) {
	// http://hilios.github.io/jQuery.countdown/examples.html
	$output .= "<script>appendScript('gadgets/meditation/countdown.js');</script>";
}

$set_counter = $GLOBALS['set_counter'] ++;
$add_js2 .= "
$(document).ready(function (){ 
	$('#defaultCountdown$set_counter')
	    .on('update.countdown', function(event) {
	      var format = '%H:%M:%S';
	      if(event.offset.days > 0) {
	        format = '%-d Tag%!d:e; ' + format;
	      }
	      if(event.offset.weeks > 0) {
	        format = '%-w Woche%!w:n; ' + format;
	      }
	      $(this).html(event.strftime(format));
	    })
	    .on('finish.countdown', function(event) {
	      $(this).parent()
	        .addClass('disabled')
	        .html('Timer ist abgelaufen...');
		});
	
	$('#defaultCountdown$set_counter').countdown('$date $time');	
	
}); ";

$output .= "<span id='defaultCountdown$set_counter'>Timer</span>";