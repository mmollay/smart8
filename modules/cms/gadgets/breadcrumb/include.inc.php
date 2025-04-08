<?
include_once (__DIR__ . '/function.inc.php');
$GLOBALS['index_id'] = call_smart_option($_SESSION['smart_page_id'], '', 'index_id');

// $output .= "<div class='smart_content_container breadcrumb_field'>";
$output .= "<a onclick=\"CallContentSite('" . $GLOBALS['index_id'] . "')\">Home</a>" . fu_ausgabe_navigpfad($_SESSION['site_id'], $ii = 0);
//$output .= "</div>";