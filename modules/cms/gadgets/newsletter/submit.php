<?php
/*
 * Save EMail in Newslettersystem
 * mm@ssi.at 21.11.2016
 * UPDATE: 23.05.2017 - Now with save Info after Reg.
 */
include ('../config.php');
include (__DIR__ . '/../function.inc.php');

$setting = $_POST ['setting' . $camp_key];
$layer_id = $_POST ['layer_id'];
$intro = $_POST ['intro'];
$firstname = $_POST ['firstname'];
$secondname = $_POST ['secondname'];
$zip = $_POST ['zip'];
$email_to = $_POST ['email'];
$camp_key = $_POST ['camp_key'];
$set_newsletter = 1;

/**
 * **************************************************************
 * Newsletter vervollständigen - und automatische Weiterleitung
 * **************************************************************
 */

// Inhalte Vervollständigen
if ($setting == 'to_complete') {
	$array = array ('sex','firstname','secondname','zip' );

	// Speichern der Eingabefelder für den Client
	foreach ( $array as $key ) {
		$value = $_POST [$key];
		if ($value) {
			$GLOBALS ['mysqli']->query ( "UPDATE {$cfg_mysql['db_nl']}.contact SET $key = '$value' WHERE verify_key = '$client_token' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		}
	}

	// URL oder Link auslesen aus dem System
	$array_label = call_layer_parameter ( $layer_id );
	// $url = $array_label['button_url'];
	// $link = $array_label['button_link'];
	$url = $button_url;
	$link = $button_link;
	$href = '';

	if ($url and $_SESSION ['admin_modus'])
		$href = "?site_select=$url";
	else {
		// Auslesen der aktuellen Seite über die Datenbank durch Verwendung der site_id
		$matches [1] = $url;
		call_smart_option ( $page_id, '', '', true );
		$href = change_link ( $matches );
	}

	if ($link) {
		if (! preg_match ( '[http]', $link )) {
			$link = "http://$link";
		}
		$href = "$link";
	}

	echo "$('.center_content').prepend(\"<div class='ui active inverted dimmer'><div class='ui text loader'>Weiterleitung erfolgt...</div></div>\");";

	if ($button_target) {
		echo "	window.open('$href','_blank');";
	} else {
		echo "window.location.replace('$href');";
	}
	return;

	exit ();
}

include_once (__DIR__ . '/include_submit.inc.php');

//echo "alert($message);";

if ($message == 'ok') {
	if ($link_reg) {
		echo "$('#newsletter_content" . $layer_id . "').html(\"<div class='message ui'>Weiterleitung erfolgt..</div>\");";
		echo "window.location.href = \"$link_reg\" ";
		exit ();
	} else {
		$text_reg = preg_replace ( "/\r|\n/", "", $text_reg );
		$text_reg = preg_replace ( "/\"/", "'", $text_reg );
		//$text_reg = change_temp ( $text_reg );
		echo "$('#newsletter_content" . $layer_id . "').html(\"<div class='message ui'>$text_reg</div>\")";
	}
	// echo $text_reg;
} else
	echo "alert ( 'Fehler beim versenden: $message'  );";

//change template
function change_temp($text) {
	return preg_replace_callback ( '!{%(.*?)%}!', function ($matches) {
		global $_POST;
		return $_POST [$matches [1]];
	}, $text );
}
	