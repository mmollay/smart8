<?php
include_once (__DIR__ . '/../../library/phpthumb/ThumbLib.inc.php');
include_once (__DIR__ . '/function.php');

$folder_relativ = $folder;

// $_SESSION['PATH_RELATIVE_EXPLORER'] ...wird erzeugt in der config.inc.php

// Dieser Pfad wird im dynamic/call_site.php erzeugt wenn ein Content von einer anderen Seite eingebunden wird
if ($GLOBALS ['dynamic_path'])
	$folder = $GLOBALS ['dynamic_path'] . '/' . $folder;
else if ($GLOBALS ['set_ajax']) // New Insert (Ajax)
	$folder = "../../../../" . $_SESSION ['PATH_RELATIVE_EXPLORER'] . '/' . $folder;
else
	$folder = "../.." . $_SESSION ['PATH_RELATIVE_EXPLORER'] . '/' . $folder;

if (! $sort)
	$sort = 'name'; // sort by name|height|width|name|title

// AUSFÜHRUNG

if ($thumbHeight)
	$thumb_height = $thumbHeight;
if ($thumbWidth)
	$thumb_width = $thumbWidth;

if (! $thumb_width) {
	$thumb_width = '300';
}
if (! $thumb_height) {
	$thumb_height = '300';
}

$thumb_folder = "$folder/thumb_gallery";

// Generate Thumbnails-folder
if (! is_dir ( $thumb_folder )) {
	exec ( "mkdir $thumb_folder" );
}

// Generate Folder - falls nicht vorhanden anzeigen als Warntext
if (! is_dir ( $folder )) {
	exec ( "mkdir $folder" );
	if (! is_dir ( $folder )) {
		$output = "<font color=red>Folder nicht vorhanden</font>";
	}
}
//$output .= $folder;
if (is_dir ( $folder )) {
	$folder_relativ = ltrim ( $folder_relativ, '/' );
	// Read Images from folder

	if ($handle = opendir ( $folder )) {
		while ( false !== ($name = readdir ( $handle )) ) {
			$image_thumb_path = $thumb_folder . '/' . $name;
			$image_path = $folder . "/" . $name;
			
			// Prüft ob image auch image ist
			if (is_array ( getimagesize ( $image_path ) )) {
				// Call Text from db
				$mysql_query = $GLOBALS ['mysqli']->query ( "SELECT * FROM smart_explorer WHERE page_id = {$_SESSION['smart_page_id']} and name='$name' and (folder='/$folder_relativ/' or folder='$folder_relativ/') " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
				$mysqli_fetch_array = mysqli_fetch_array ( $mysql_query );
				$title = $mysqli_fetch_array ['title'];
				$text = $mysqli_fetch_array ['text'];
				$target = $mysqli_fetch_array ['target'];
				$link = $mysqli_fetch_array ['link'];
				$style = $mysqli_fetch_array ['style'];
				$style_align = $mysqli_fetch_array ['style_align'];
				$style_width = $mysqli_fetch_array ['style_width'];
				$link_intern = $mysqli_fetch_array ['link_intern'];
				// if ($text) $title = $title." ($text)";

				if (AllowFormat ( $name, 'jpg;jpeg;gif;png;' ) && is_file ( $image_path ) == 'true' && $modul != 'player') {
					list ( $width, $height, $type, $attr ) = getimagesize ( $image_path );

					if ($thumb_height or $thumb_width) {

						list ( $thumb_width1, $thumb_height1, $type, $attr ) = @getimagesize ( $image_thumb_path );

						if (($thumb_height != $thumb_height1 and $thumb_width != $thumb_width1) or ! is_file ( $image_thumb_path )) {
							// Verkleinert ein Bild auf Webtaugliche Groesse

							$thumb = PhpThumbFactory::create ( $image_path );

							// for thumbnails (cut image for better format
							if ($parameter ['adaptiveResize']) {
								// Wenn es sich um keinen Zahlenwert handelt
								if (! is_numeric ( $thumb_width )) {
									$thumb_width = '200';
								}
								if (! is_numeric ( $thumb_height )) {
									$thumb_height = '200';
								}

								$thumb->resize ( 1000, $thumb_height );
								$thumb->adaptiveResize ( $thumb_width, $thumb_height );
							} else {
								$thumb->resize ( $thumb_width, $thumb_height );
							}
							$thumb->save ( $image_thumb_path );
						}
					}
					/*
					 * Set Date for Image - IMPORTANT!!!! - for reload without empty the cache after changing
					 */
					$file_time1 = filemtime ( $image_path );

					if (is_file ( $image_thumb_path ))
						$file_time2 = filemtime ( $image_thumb_path );
					else
						$file_time2 = filemtime ( $image_path );

					$image_path = "$image_path" . "?" . $file_time1;
					$image_thumb_path = "$image_thumb_path" . "?" . $file_time2;
					$image_result [] = array ('path' => $image_path,'date' => $file_time1,'thumb_path' => $image_thumb_path,'thumb_height' => $thumb_height1,'thumb_width' => $thumb_width1,'height' => $height,'width' => $width,'name' => $name,'title' => $title,'text' => $text,'link' => $link,'style' => $style,'style_width' => $style_width,'style_align' => $style_align,'link_intern' => $link_intern,'size' => $size,'target' => $target,'align' => $align );
				} else if (AllowFormat ( $name, 'mp3;' ) && is_file ( $image_path ) == 'true') {

					$path_parts = pathinfo ( $name );

					$image_result [] = array ('folder' => $folder,'filename' => $path_parts ['filename'],'path' => $image_path,'date' => $file_time1,'thumb_path' => $image_thumb_path,'thumb_height' => $thumb_height1,'thumb_width' => $thumb_width1,'height' => $height,'width' => $width,'name' => $name,'title' => $title,'text' => $text,'link' => $link,'style' => $style,'style_width' => $style_width,'style_align' => $style_align,'link_intern' => $link_intern,'size' => $size,'target' => $target,'skin' => $skin );
				}
			}
		}
		closedir ( $handle );
	}
}

if (! is_array ( $image_result )) {
	$output .= " <div class='ui message info' style='text-align:center'>Keine Dateien für die Galerie vorhanden</div>";
	return;
}

foreach ( $image_result as $res )
	$sortAux [] = $res [$sort];

if (! $GLOBALS ['set_ajax'])
	$_SESSION ['set_collagePlus'] = '';

if (! $direction or $direction == asc) // Aufsteigend
	array_multisort ( $sortAux, SORT_ASC, $image_result );
else // Absteigend
	array_multisort ( $sortAux, SORT_DESC, $image_result );

if ($representation == 'collagePlus' or ! $representation)
	$representation = 'fleximages';

if ($representation == 'flexslider2')
	$representation = 'carousel';

// elseif ($representation == 'carousel') {
// $slide_view = 'carousel';
// $representation = 'flexslider2';
// }

// if (! is_file ( "$representation/include.inc.php" ))
// $representation = 'fleximages';

if ($representation) {
	include ("$representation/include.inc.php");
	return;
}

if (! $output) {
	$output = "<div class='message ui'><div align=center><br>Galerie noch nicht definiert<br><br></div></div>";
}