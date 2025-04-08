<?php
$verify_key = get_verify_form_user ();
if (!$button) $button = "Anmelden";

if ($_SESSION['company'] == 'inbs') {
	$register_link = "center.inbs.at";
}elseif ($_SESSION['company'] == 'em') {
	$register_link = "center.em-gemeinschaft.at";
}else {
	$register_link = "center.ssi.at";
}

$output = "<a class='ui $color button' href='$register_link/?verify_key=$verify_key' target='new_page' >$button</a>";