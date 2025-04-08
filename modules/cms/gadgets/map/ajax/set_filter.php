<?php
/*
 * hier wird standartmässig "autofit" auf 1 gesetzt
 * Dies erfolgt beim laden der Seite und wird über JS aufgerufen
 */
session_start ();
$_SESSION["map_filter"]['autofit'] = 1;
// $_SESSION["map_filter"]['bicyclinglayer'] = $_POST['bicyclinglayer'];

//setcookie ( "autofit", $_SESSION["map_filter"]['autofit'], time () + 3600 );
//setcookie ( "bicyclinglayer", $_SESSION["map_filter"]['bicyclinglayer'], time () + 3600 );
