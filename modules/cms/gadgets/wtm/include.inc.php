<?php

// Hier weren CSS und JS sowei din Injection geladen
$output .= "<div id=call_events></div>";

// MODAL für die Anmeldung
$output .= "
<div id='modal_login' class='ui small modal'>
	<i class='close icon'></i>
	<div class='header'>Anmelden</div>
	<div class='content' id=modal_content_login></div>
</div>";

$GLOBALS['add_path_js'] .= "<script>var group_id = '$group_id';</script>";
$GLOBALS['add_path_js'] .= "<script type='text/javascript' src='gadgets/wtm/wtm.js'></script>";