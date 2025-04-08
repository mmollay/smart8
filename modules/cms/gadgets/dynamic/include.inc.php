<?php
//if (! $GLOBALS[set_element_dynamic] && $_SESSION['admin_modus']) {
// $add_js2 .= "
// $(document).ready(function (){
// // Verändert bei mousemove den Hintergrund
// $('.dynamic_element').hover( function() {
// if (Cookies.get('edit_modus') == 'on') {
// //$(this).addClass('result_hover');
// }
// }, function() {
// if (Cookies.get('edit_modus') == 'on') {
// $(this).removeClass('result_hover');
// }
// });

// $('.dynamic_element').popup({
// title : 'Dynamische Element',
// content : 'Zur Bearbeitung der Einstellungen, bitte Bezugselement wählen!'
// });

// });";
//}

// ADMIN
if ($_SESSION['admin_modus']) {
	// site_id check
	$query = $GLOBALS['mysqli']->query ( "SELECT site_id FROM smart_layer LEFT JOIN smart_langLayer ON smart_layer.layer_id=smart_langLayer.fk_id WHERE layer_id = '$select_dynamic' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	// AND lang='{$_SESSION['page_lang']}'
	$array = mysqli_fetch_array ( $query );
	$button_call_site = "<div class=\"ui button mini\" onclick=\"CallContentSite({$array['site_id']})\">Seite aufrufen</div>";
}

if ($_SESSION['admin_modus']) {
	$add_js2 .= "
	$(document).ready(function (){
		$('#$select_dynamic').popup({
			position : 'top center',
			delay: {
		      show: 300,
		      hide: 300
		    },
			inline     : true,
    			hoverable  : true,
			html  : '<b>Dynamisches Element:</b><br> Zur Bearbeitung der Einstellungen, bitte Bezugselement wählen!:$button_call_site'
		});
	});
	";
}


//$GLOBALS[set_element_dynamic] = true;

if ($select_dynamic) {
	$add_path_js .= "<script type='text/javascript' src='gadgets/dynamic/dynamic.js'></script>";
	$output = "<div class='dynamic_element' id='$select_dynamic'></div>";
	
	if ($GLOBALS['set_ajax']) {
		$output .= $GLOBALS['add_js2'];
	}
}

// Aufruf wenn noch kein ELement gewählt wurde
if (! $output && $_SESSION['admin_modus']) {
	$output = "<br>
		<div class='ui mini message'><br>
		<div class='ui button' onclick=\"call_element_setting(15976,'dynamic')\" >Dynamisches Element wählen</div><br><br>		
		</div>";
}