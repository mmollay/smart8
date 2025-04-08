<?
/************************************************************************************
 * COUNTDOWN
 ************************************************************************************/
if (! $GLOBALS['set_counter']) {
	// http://hilios.github.io/jQuery.countdown/examples.html
	$add_path_js .= "<script type='text/javascript' src='gadgets/meditation/countdown.js'></script>";
	// $add_js2 .= "<script type='text/javascript' src='../ssi_smart/js/ion.sound/ion.sound.min.js'></script>";
}

$set_counter = $GLOBALS['set_counter'] ++;

$newTime = date ( "Y-m-d H:i:s", strtotime ( date ( "Y-m-d H:i:s" ) . " +$time minutes" ) );

$set_date = explode ( "-", $date );

//Zählt die Meditationen mit
$add_jquery = "
		$.ajax( {
		url      : 'gadgets/meditation/time_save.php',
		global   : false,
		async    : false,
		type     : 'POST',
		data     : ({'time' : $('#time-selector').val() }),
		dataType : 'html',
		success : function (data) {
			$('#time-controller').append(data);
		}
	});
";

$add_js2 .= "
		$(document).ready(function (){  
			$('#time-selector').dropdown();
			$('#time-start').click( function () {
				$('#time-show$set_counter').hide().html('Es geht los...').fadeIn('slow');
				$('#time-controller').hide();
				setTimeout(function(){
					ion.sound({ sounds: [{name: 'gong2'}], path: 'gadgets/meditation/sounds/', preload: true, volume: 1.0 });
					ion.sound.play('gong2');
					setTimeout(function(){
						var val = $('#time-selector').val().toString().match(/^([0-9\.]{1,})([a-z]{1})$/),
		        		qnt = parseFloat(val[1]),
		        		mod = val[2];
			   			switch(mod) {
					      case 's':
					        val = qnt * 1000;
					        break;
					      case 'm':
					        val = qnt * 60 * 1000;
					        break;  
					      case 'h':
					        val = qnt * 60 * 60 * 1000;
					        break;
					      case 'd':
					        val = qnt * 24 * 60 * 60 * 1000;
					        break;
					      case 'w':
					        val = qnt * 7 * 24 * 60 * 60 * 1000;
					        break; // Break here to no enter the else value
					      default:
					        val = 0;
					    }
			   			selectedDate = new Date().valueOf() + val;
					
						$('#time-show$set_counter').countdown(selectedDate, function(event) {
							$(this).html(event.strftime('%M:%S'));
						})
						.on('finish.countdown', function(event) {
							$(this).html('Meditation ist beendet <i class=\"icon yellow heard\"></i>').parent(); 
							ion.sound({ sounds: [{name: 'gong2'}], path: 'gadgets/meditation/sounds/', preload: true, volume: 1.0 });
							ion.sound.play('gong2');
							$('#time-controller').fadeIn();
							$add_jquery
						});
					},1000);
					
				}, 4000);
				
			}); 
		});";

// <option value="5s" selected>5sec</option>// zu testen

$output .= "<h2 class='ui grey header'><div align=center><span id='time-show$set_counter'>Meditations-Timer</span></div></h2>";
$output .= '<div id="time-controller">
	<select class="dropdown ui" id="time-selector">
	<option value="5m">5 min</option>
	<option value="10m">10 min</option>
    <option value="15m">15 min</option> 
	<option value="30m">30 min</option>
	<option value="1h">1 h</option>
  	</select>
	<button id=time-start class="button ui green" >Start</button>
</div>';
