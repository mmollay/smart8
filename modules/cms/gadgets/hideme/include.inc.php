<?
if ($select == 'produce') {
	
	$add_path_js .= "<script type='text/javascript' src='gadgets/hideme/hideme_produce.js'></script>";
	
	$output .= "<button style='display: none' class='ui circular green icon button' id=button-show-container-hideme><i class='icon arrow left'></i> Zurück</button>";
	//Platzhalter für das Script
	$output .= "<div id=container-hideme></div>";
	//<div> für die Anzeige Download
	$output .= "<div id='segment-download' class='ui secondary segment' style='display: none' align=center><div id='download-div'></div><br>$successful_message</div>";
	//Anzeige für Fehlermeldung
	$output .= "<div id='error-div' class='ui message red' style='display: none'></div>";
}

if ($select == 'unpacking') {
	$add_path_js2 .= "<script type='text/javascript' src='gadgets/hideme/hideme_unpacking.js'></script>";
	
	$output .= "<button style='display: none' class='ui circular green icon button' id=button-show-container-hideme><i class='icon arrow left'></i> Zurück</button>";
	//Platzhalter für das Script
	$output .= "<div id=container-hideme></div>";
	//<div> für die Anzeige Download
	$output .= "<div id='segment-download' class='ui secondary segment' style='display: none' align=center><div id='download-div'></div><br>$successful_message</div>";
	//Anzeige für Fehlermeldung
	$output .= "<div id='error-div' class='ui message red' style='display: none'></div>";
}	