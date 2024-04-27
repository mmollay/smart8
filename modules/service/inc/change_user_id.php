<?php
include_once ('../../login/config_main.inc.php');

/******************************************************/
$user_id_old = '40';
// $user_id_new = '40';
$user_id_new = '1076';
$company_old = 'ssi';
$company_new = 'obststadt';
$company_id = '8';
$folder_path_old = "$company_new/user$user_id_old/";
$folder_path_new = "$company_new/user$user_id_new/";

$db_array = array ( 'email_setting' , 'module2id_user' , 'register' , 'smart_explorer' , 'smart_feedback_traffic' , 'smart_gadget_meditation' , 'smart_information' , 'smart_page' );
/******************************************************/

//user_id alt ersetzen auf user_id neu
foreach ( $db_array as $key => $db ) {
	$GLOBALS['mysqli']->query ( "UPDATE `ssi_smart$company_id`.`$db` SET `user_id` = '$user_id_new' WHERE `user_id` = $user_id_old;" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	// DB-Sätze ohne user_id wird entfernt
	$GLOBALS['mysqli']->query ( "DELETE FROM `ssi_smart$company_id`.`$db` WHERE user_id = '' " );
}

// Auslesen der Page_id
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_page INNER JOIN smart_id_site2id_page ON smart_page.page_id = smart_id_site2id_page.page_id AND smart_page.user_id =  $user_id_new" ) or die ( mysql_quer () );
$array1 = mysqli_fetch_array ( $query );
$page_id = $array1['page_id'];
echo "page_id = $page_id<br>";

// Tauscht den Pfad im Layout aus
$GLOBALS['mysqli']->query ( "UPDATE smart_layout SET `layout_array` = replace(layout_array, '$folder_path_old', '$folder_path_new') WHERE page_id='$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
// Tauscht den Pfad Kopf und Fusszeile
$GLOBALS['mysqli']->query ( "UPDATE smart_content SET `content` = replace(content, '$folder_path_old', '$folder_path_new') WHERE page_id='$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );

// Auflisten der Seiten
while ( $array = mysqli_fetch_array ( $query ) ) {	
	$site_id = $array['site_id'];
	echo "site_id = $site_id<br>";
	$query2 = $GLOBALS['mysqli']->query("SELECT * from smart_layer WHERE site_id = $site_id");
	while ( $array2 = mysqli_fetch_array ( $query2 ) ) {
		$layer_id = $array2['layer_id'];
		echo "layer_id = $layer_id<br>$folder_path_old - $folder_path_new";
		// Tauscht die Pfade in den Texten um
		$GLOBALS['mysqli']->query ( "UPDATE smart_langLayer SET `text` = replace(text, '$folder_path_old', '$folder_path_new') WHERE fk_id='$layer_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
}

echo "ok";