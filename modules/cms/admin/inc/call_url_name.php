<?php
//include_once (__DIR__ . '/../function.inc.php');
include_once (__DIR__ . '/../../../login/config_main.inc.php');

$url_name = $_POST['url_name'];
if ($_POST['page_id'])
	$page_id = $_POST['page_id'];
else
	$page_id = $_SESSION['smart_page_id'];

if ($url_name) {
	// Nachprüfen ob es den Namen schon gibt
	$query = $GLOBALS['mysqli']->query ( "SELECT * from smart_langSite t1 LEFT JOIN smart_id_site2id_page t2 ON t2.site_id = t1.fk_id  WHERE site_url = '$url_name' AND page_id = '$page_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	if (mysqli_num_rows ( $query )) {
		echo strtolower ( seo_permalink (trim( $url_name ) . "_new" ));
	} else {
		echo strtolower ( seo_permalink (trim( $url_name ) ));
	}
}
		