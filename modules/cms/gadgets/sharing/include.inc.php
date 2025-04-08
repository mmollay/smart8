<?php
// http://js-socials.com/demos/
if (! $view)
	$view = 'default';

if ($url) {
	$add_jssicials .= "url: '$url', ";
}

if ($text) {
	$add_jssicials .= "text: '$text', ";
}

if (! $jssocials_theme)
	$jssocials_theme = 'jssocials-theme-classic';

// $view = semantic;

	if ($fontsize) {
		$add_fontsize = "style='font-size:{$fontsize}px;' ";
	}
	
// $link = "http://www.habdawas.at";
$data['default'] .= "<div align=$align><div $add_fontsize id='share_buttons'></div></div>";

$add_css2 .= "<link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.7.0/css/all.css' integrity='sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ' crossorigin='anonymous'>";

$output = $data[$view];

// if ($logo) {
// $add_jssicials_share = "shares: [{ share: 'facebook', logo: '$logo' },{ share: 'whatsapp', logo: '$logo'},{ share: 'twitter', logo: '$logo'}]";
// } else

$array_social = array ( "facebook" , "whatsapp" , "twitter" , "googleplus" , "linkedin" , "pinterest" , "stumbleupon" , "pocket" , "viber" , "messenger" , "vkontakte" , "telegram" , "line" , "rss" , "email" );

foreach ( $array_social as $value ) {
	if (${"social_$value"})
		$share_values .= "'$value',";
}

if ($showLabel)
	$add_jssicials .= "showLabel: true,";
else {
	$add_jssicials .= "showLabel: false,";
}
if ($showCount)
	$add_jssicials .= "showCount: true,";
else {
	$add_jssicials .= "showCount: false,";
}
if ($shareIn)
	$add_jssicials .= "shareIn: 'popup',";

$output .= "<link rel='stylesheet' href='gadgets/sharing/jssocials.css' />";
$output .= "<link rel='stylesheet' type='text/css' href='gadgets/sharing/$jssocials_theme.css' />";
$output .= "\n<script type='text/javascript' src='gadgets/sharing/jssocials.js'></script>";
$output .= "\n<script>$(document).ready(function() { $('#share_buttons').jsSocials({ $add_jssicials shares: [ $share_values ] }); });</script>";


// http://js-socials.com/demos/
// $view = semantic;

// $link = "http://www.habdawas.at";
// $data['default'] .= "<div align=$align><div id='share_buttons'></div></div>";

// $add_css2 .= "<link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.7.0/css/all.css' integrity='sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ' crossorigin='anonymous'>";
// $add_css2 .= "<link rel='stylesheet' href='gadgets/sharing/jssocials.css' />";
// // $add_css2 .= "<link rel='stylesheet' type='text/css' href='gadgets/sharing/jssocials-theme-flat.css' />";
// $add_css2 .= "<link rel='stylesheet' type='text/css' href='gadgets/sharing/jssocials-theme-classic.css' />";

// $output = $data[$view];

// $output .= "\n<script type='text/javascript' src='gadgets/sharing/jssocials.js'></script>";
// $output .= "\n<script>$('#share_buttons').jsSocials({ $add_jssicials  shares: [ 'facebook', 'whatsapp','twitter']  }); </script>";
	
	
	