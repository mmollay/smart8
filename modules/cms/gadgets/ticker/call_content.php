<?php

include ("../config.php");
function linkify($string) {
	// Pattern von oben einfügen.
	$regex = "/( (?:
	(?:https?|ftp) : \\/*
	(?:
	(?: (?: [a-zA-Z0-9-]{1,} \\. )+
	(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
	| aero | biz | coop | info | museum | name | pro
	| example | invalid | localhost | test | local | onion | swift ) )
	| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
	| (?: [0-9A-Fa-f:]+ : [0-9A-Fa-f]{1,4} )
	)
	(?: : [0-9]+ )?
	(?! [a-zA-Z0-9.:-] )
	(?:
	\\/
	[^&?#\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]*
	)?
	(?:
	[?#]
	[^\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]+
	)?
	) | (?:
	(?:
	(?: (?: [a-zA-Z0-9-]{2,} \\. )+
	(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
	| aero | biz | coop | info | museum | name | pro
	| example | invalid | localhost | test | local | onion | swift ) )
	| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
	)
	(?: : [0-9]+ )?
	(?! [a-zA-Z0-9.:-] )
	(?:
	\\/
	[^&?#\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]*
	)?
	(?:
	[?#]
	[^\\(\\)\\[\\]\\{\\}<>\\'\\\"\\x00-\\x20\\x7F-\\xFF]+
	)?
	) | (?:
	[a-zA-Z0-9._-]{2,} @
	(?:
	(?: (?: [a-zA-Z0-9-]{2,} \\. )+
	(?: arpa | com | org | net | edu | gov | mil | int | [a-z]{2}
	| aero | biz | coop | info | museum | name | pro
	| example | invalid | localhost | test | local | onion | swift ) )
	| (?: [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} \\. [0-9]{1,3} )
	)
	) )/Dx";
	$string = htmlspecialchars ( $string );
	
	if (! function_exists ( 'valid_url' )) {
		function valid_url($url) {
			if (substr ( $url[0], 0, 7 ) != 'http://') {
				$valid_url = 'http://' . $url[0];
			} else {
				$valid_url = $url[0];
			}
			return '<a class="link" target="new" href="' . $valid_url . '">' . $url[0] . '</a>';
		}
	}
	
	$output = preg_replace_callback ( $regex, 'valid_url', $string );
	return $output;
}

$layer_id = $_POST['layer_id'];

$query = $GLOBALS['mysqli']->query ( "SELECT text FROM smart_gadget_ticker WHERE layer_id = '$layer_id' ORDER BY timestamp desc" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );
$text = $array['text'];
if (!$text) $text = 'Noch kein Tickertext vorhanden';

echo linkify($text);
