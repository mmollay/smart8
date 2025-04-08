<?php
// mm@ssi.at am 04.10.2017
// Ruft Neuen Content von Seite auf, dieser wird mit Ajax übergeben
include('../../../login/config_main.inc.php');
include (__DIR__ . '/../../library/css_umwandler.inc');
include (__DIR__ ."/../../inc/load_css.php");
include (__DIR__ . "/../../inc/load_content.php");

$site_id = $_POST['site_id'];

echo load_content ( $site_id, true );