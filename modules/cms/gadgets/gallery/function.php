<?php
// include_once ($_SERVER['DOCUMENT_ROOT'] . '/ssi_smart/gadgets/gallery/include_gallery.inc.php');
function AllowFormat($name, $format) {
	/*
	 * AllowFormat() - Prueft die Gluetigkeit eines Formates
	 *
	 * @param $name Filename
	 * @param $format erlaubte Formate;
	 */

	// l�scht *
	$format = preg_replace ( "/\*./", "", $format );

	// auslesen des Formates aud dem Filenamen
	$extension = explode ( '.', $name );
	$extension = $extension[(count ( $extension ) - 1)];
	$extension = strtolower ( $extension );

	// Array erzeugen fuer gueltige Formate
	$array_format = preg_split ( "/;/", $format );
	// Pruefen ob Format in Array vorhanden ist
	if (in_array ( $extension, $array_format )) {
		return TRUE;
	}
}
