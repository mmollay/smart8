<?php
$add_css2 .= "\n<link href=\"gadgets/site_map/sitemapstyler.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />";
$add_path_js .= "\n<script type=\"text/javascript\" src=\"gadgets/site_map/sitemapstyler.js\"></script>";
// Nur Aufruf wenn von Ajax erzeugt wird
// if ($GLOBALS['set_ajax']) {
include_once (__DIR__ . '/../../library/function_menu.php');
$add_js2 .= "$(document).ready(function() { sitemapstyler(); });";
// }

$menuData = generateMenuStructure ( $_SESSION['smart_page_id'] );

// Uebergabe des Id-von der Site (Es wird nur die Sub-Struktur angezeigt
if ($substructure)
	$root_id = $_SESSION['site_id'];
else
	$root_id = 0;

$output_sitemap = buildMenu ( $root_id, $menuData, 'sitemap' );

if ($output_sitemap)
	$output .= buildMenu ( $root_id, $menuData, 'sitemap' );
elseif ($_SESSION['admin_modus']) {
	$output .= "<div class='ui message blue'>Sitemap Info:  Kein Menüpunkte für diese Ebene vorhanden!</div>";
}
// $output = buildSitemap(0,$menuData);
?>