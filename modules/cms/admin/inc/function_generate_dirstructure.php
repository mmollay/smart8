<?php
/*
 * Erzeugen einer Dir-Strukter mit .htaccess - Weiterleitung
 */

//TESTEN
/*
include_once ('../../../login/config_main.inc.php');
include_once (__DIR__ . '/../../library/function_menu.php');
include_once ('../../gadgets/function.inc.php');
$menuData = generateMenuStructure ( $_SESSION[smart_page_id], true );

$_SESSION['path_folder_absolute'] = $_SERVER['DOCUMENT_ROOT'] . $path_page_relative;

User-Page-Path

echo $_SESSION['path_folder_absolute'];
echo "<br>";
echo $path_page;
echo "<br>";
echo $path_id_explorer_folder;
echo "<hr>";
$output_menu = generate_dir_sturcture ( 0, $menuData );
echo $output_menu;
*/
function generate_dir_sturcture($parentId, $menuData, $path = false) {
	// print_r ($menuData);
	if (isset ( $menuData['parents'][$parentId] )) {
		
		foreach ( $menuData['parents'][$parentId] as $itemId ) {
			$name = $menuData['items'][$itemId]['menu_text'];
			$site_url = $menuData['items'][$itemId]['site_url'];
			$site_id = $menuData['items'][$itemId]['site_id'];
			
			$set_path = "$path/$site_url";
			$set_absolute_path = $_SESSION['path_page_absolute'] . $set_path;
			$html .= $set_absolute_path . "<br>";
			if (! is_dir ( $set_absolute_path )) {
				mkdir ( "$set_absolute_path" );
			}
			// Setzt die Endung des Skriptes html oder php
			$file_ending = check_php_script ( $site_id );
			
			if ($_SESSION['index_id'] == $site_id) {
				$site_url_ending = "index$file_ending"; // fuer Sitemapå
			} else
				$site_url_ending = $site_url . "$file_ending";
			
			// Generate .htaccess
				$data_htaccess = "#path to $site_url_ending";
			
			exec ( "touch $set_absolute_path/.htaccess" );
			$fp = fopen ( "$set_absolute_path/.htaccess", "w+" );
			@fwrite ( $fp, $data_htaccess );
			@fclose ( $fp );
			
			// find childitems recursively
			$html .= generate_dir_sturcture ( $itemId, $menuData, $set_path );
		}
	}
	return $html;
}
