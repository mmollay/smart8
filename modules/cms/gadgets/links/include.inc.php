<?php
//https://webthumb.ssi.at/cgi-bin/webcapt.fcgi?url=www.ssi.at&app=ssi&format=jpg

if (! $text) {
	$output = '<div class="ui message"><div align=center><br>Noch keine Links vorhanden<br><br></div></div>';
	return;
}
$array_link = explode ( "\n", $text );

$array_size = array ( "mini" => "35" , "tiny" => "80" , "small" => "150" , "medium" => "300", "large" => "450" );

if (!$size) $size = $array_size['tiny'];
else $size = $array_size[$size];

foreach ( $array_link as $url ) {
	$img = "<img data-tooltip='$url' src ='https://webthumb.ssi.at/cgi-bin/webcapt.fcgi?url=$url&app=ssi&format=jpg&width=$size&vwidth=$vwidth&vheight=$vheight'>";
	
	if ($url && strncasecmp ( 'http', $url, 4 )) {
		$add_http = 'http://';
	}
	if ($url)
	$list .= "<a href ='$add_http$url' target = '_new' >$img</a>";
}

$output .= "<div class='ui $size bordered images'><div align='$align'>";
$output .= $list;
$output .= "</div></div>";