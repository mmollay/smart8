<?php
require_once (__DIR__ . "/../gadgets/function.inc.php");
//require_once (__DIR__ . "/../gadgets/inc/phpthumb/ThumbLib.inc.php");

/*
 * Laden von Content im Admin-Modus mit "Verschiebe und Löschbutton
 */
function show_element($id, $new = false, $site_id = false) {
	global $str_text;
	
	include (__DIR__ . '/../inc/element.php');
	
	if ($segment_compact)
		return "<div align='$align'>$output</div>";
	else
		return "$output";
}

// Get content
// Header/Footer/Content
function call_content($id, $site_id = false) {
	global $_GET;
	// Element im publicmodus nicht anzeigen
	if (! $_SESSION ['admin_modus']) {
		$add_query = "AND hidden = '' ";
	}
	if ($site_id) {
		$add_query .= "AND site_id = '$site_id' ";
	} else {
		$add_query .= "AND page_id = '{$_SESSION['smart_page_id']}' ";
	}

	// Position
	if ($id) {
		$add_query .= "AND position = '$id'";
	} else {
		$add_query .= "AND (position = 'left' OR position = 'right' OR position = '')";
	}
	
	// Content von der Fusszeile aufrufen
	$mysql_query2 = $GLOBALS ['mysqli']->query ( "
			SELECT * FROM smart_layer 
			WHERE 1  
			$add_query
			AND splitter_layer_id = '0'
			AND archive = ''
			order by sort,layer_id
			" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	while ( $array2 = mysqli_fetch_array ( $mysql_query2 ) ) {
	    
		// $layer_content = $array2['text'];
		$gadget = $array2 ['gadget'];

		$_SESSION ['load_js'] [$gadget] = true;
		$layer_id = $array2 ['layer_id'];
		$field = $array2 ['field'];
		$position = $array2 ['position'];
		$layer_fixed = $array2 ['layer_fixed'];
		
		$content .= show_element ( $layer_id, '', $site_id );
		
		// Verkleinern von Bildern im System + Sicherung der Bilder anlegen
		// $layer_content = image_resizer($layer_content);
		// $layer_content = UmwandelnTemplates($layer_content,$layer_id);

		// siehe libray/function
		// $layer_content = change_resize ( $layer_content );
	}
	return $content;
}

/*
 * Function - Verkleinern der Bilder, Abspeichern Folder, und verknuepfen mit dem Content
 */
function change_resize($content) {
	$resize_folder_name = "autoresize";

	// Auslesen der Attributes der Bilder
	$array = get_tag_attributes ( $content, 'img' );

	foreach ( $array as $array2 ) {

		// Eingestellte Breite im Content
		$listet_width = $array2 ['width'];
		// Eingestellt Höhe im Content
		$listet_height = $array2 ['height'];

		// pfad + name
		$src = $array2 ['src'];

		// löscht den subfolder aus zum erzeugen des neuen bildes falls ein autoresize vorhanden ist
		$src = preg_replace ( "[$resize_folder_name/]", "", $src );

		$path_array = pathinfo ( $src );

		// Pfad ohne Bildnamen
		$dirname = $path_array ['dirname'];

		// Nur der Name des Bildes
		$basename = $path_array ['basename'];
		// $basename_resize = "$listet_width" . "_" . "$listet_height" . "_$basename";
		$basename_resize = "$basename";

		// Pfad und Name
		$dirname_basenname = $dirname . "/" . $basename;

		// Pfad mit Resize (fuer das anlegen von Foldern)
		$dirname_resize = $dirname . "/$resize_folder_name";

		// Pfad mit Resize und Name
		$dirname_resize_basenname = $dirname_resize . "/" . $basename_resize;

		// absoluter Pfad
		$doc_root = $_SERVER ['DOCUMENT_ROOT'];

		// absoluter Pfad + dirname
		$src_doc_root_dirname = $doc_root . "/" . $dirname . "/";

		// absoluter Pfad + dirname + basename
		$src_doc_root_dirname_basename = $src_doc_root_dirname . $basename;
		$src_doc_root_dirname_basename = preg_replace ( "[//]", "/", $src_doc_root_dirname_basename );
		// absoluter Pfad zu Resizefolder
		$src_doc_root_dirname_resizefolder = $src_doc_root_dirname . "$resize_folder_name/";

		// absoluter Pfad zu Resizefolder + basename
		$src_doc_root_dirname_resizefolder_basename = $src_doc_root_dirname_resizefolder . $basename_resize;

		$src_doc_root_dirname_resizefolder_basename = preg_replace ( "[//]", "/", $src_doc_root_dirname_resizefolder_basename );
		// prueft ob Bild mit der korrekten Groesse vorhanden ist

		if (preg_match ( "[$resize_folder_name]", $src_doc_root_dirname_resizefolder_basename )) {
			list ( $resize_width, $resize_height, $type ) = @getimagesize ( $src_doc_root_dirname_resizefolder_basename );
		}

		// Originaler Wert auslesen
		list ( $width, $height, $type ) = @getimagesize ( $src_doc_root_dirname_basename );
		// Originale Breite des Bildes
		$orig_width = $width;
		// Originale Höhe des Bildes
		$orig_height = $height;

		// Resize Folder anlegen wenn noch nicht vorhanden ist
		if (! is_dir ( $src_doc_root_dirname_resizefolder )) {
			// echo $src_doc_root_dirname_resizefolder;
			@mkdir ( $src_doc_root_dirname_resizefolder, 0755 );
		}

		/*
		 * Wenn das Bild kleiner ist als das Original oder das verkleinerte Bild verändert wurde
		 * - Anlegen eines neuen Bildes in der "resize"-Datei
		 * - Umschreiben im Content
		 */

		if ($listet_width != $orig_width and $resize_width != $listet_width and is_file ( $src_doc_root_dirname_basename )) {
			// if (is_file ( $src_doc_root_dirname_basename )) {
			// echo $src_doc_root_dirname_basename."<br>";
			// echo $src_doc_root_dirname_resizefolder_basename."<br>";
			// echo $listet_width;

			$thumb = PhpThumbFactory::create ( $src_doc_root_dirname_basename );
			$thumb->resize ( $listet_width )->save ( "$src_doc_root_dirname_resizefolder_basename" );
			$content = preg_replace ( "[$dirname_basenname]", "$dirname_resize_basenname", $content );
			// }
		}
	}
	// echo "<pre>";
	// print_r ( $array );
	// echo "</pre>";
	return $content;
}

/*
 * HTML/XML Tag Attribute ermitteln
 * http://andreas.droesch.de/2009/09/php-html-xml-tag-attribute-ermitteln/
 * Beispielaufruf 1: Nur Img-Tags durchsuchen
 * $tags = get_tag_attributes($html_code, 'img');
 * Beispielaufruf 2: Nur Div- und P-Tags durchsuchen
 * $tags = get_tag_attributes($html_code, array('div', 'p'));
 * Beispielaufruf 3: Alle Tags durchsuchen
 * $tags = get_tag_attributes($html_code);
 * Ausgabe "array"
 */
function get_tag_attributes($code, $tag_search = false) {
	// Einzelnen Tagnamen in ein Array packen
	if ($tag_search && ! is_array ( $tag_search )) {
		$tag_search = array ($tag_search );
	}

	// Alle Tags auslesen
	$matches = array ();
	preg_match_all ( '/<([a-z]*?) (.*?)\>/is', $code, $matches );

	// Funde durchlaufen und Attribute ermitteln
	$tags = array ();
	foreach ( $matches [1] as $key => $tag_name ) {
		// Nicht zugelassene Tags überspringen
		if ($tag_search) {
			if (! in_array ( strtolower ( $tag_name ), $tag_search )) {
				continue;
			}
		}

		// Attribute austrennen
		$attributes = array ();
		preg_match_all ( '/([a-z]*?)=(\".*?"|\'.*?\')/is', $matches [2] [$key], $attributes );

		// Attribute abspeichern
		$tag = array ('tag' => $tag_name );
		foreach ( $attributes [1] as $key => $value ) {
			$tag [$value] = substr ( $attributes [2] [$key], 1, - 1 );
			if ($value == 'style') {
				$array1 = preg_split ( "/;/", $tag [$value] );
				foreach ( $array1 as $value1 ) {
					if ($value1) {
						$array2 = preg_split ( "/:/", $value1 );
						$key = trim ( $array2 [0] );
						$value = trim ( $array2 [1] );
						$tag [$key] = preg_replace ( "/px/", "", $value );
					}
				}
			}
		}
		$tags [] = $tag;
	}

	// Daten zurückgeben
	return $tags;
}
function ReSizeImagesInHTML($HTMLContent, $MaximumWidth, $MaximumHeight) {

	// find image tags
	preg_match_all ( '/<img[^>]+>/i', $HTMLContent, $rawimagearray, PREG_SET_ORDER );

	// put image tags in a simpler array
	$imagearray = array ();
	for($i = 0; $i < count ( $rawimagearray ); $i ++) {
		array_push ( $imagearray, $rawimagearray [$i] [0] );
	}

	// put image attributes in another array
	$imageinfo = array ();
	foreach ( $imagearray as $img_tag ) {

		preg_match_all ( '/(src|width|height)=("[^"]*")/i', $img_tag, $imageinfo [$img_tag] );
	}

	// combine everything into one array
	$AllImageInfo = array ();
	foreach ( $imagearray as $img_tag ) {

		$ImageSource = str_replace ( '"', '', $imageinfo [$img_tag] [2] [0] );
		$OrignialWidth = str_replace ( '"', '', $imageinfo [$img_tag] [2] [1] );
		$OrignialHeight = str_replace ( '"', '', $imageinfo [$img_tag] [2] [2] );

		$NewWidth = $OrignialWidth;
		$NewHeight = $OrignialHeight;
		$AdjustDimensions = "F";

		if ($OrignialWidth > $MaximumWidth) {
			$diff = $OrignialWidth - $MaximumHeight;
			$percnt_reduced = (($diff / $OrignialWidth) * 100);
			$NewHeight = floor ( $OrignialHeight - (($percnt_reduced * $OrignialHeight) / 100) );
			$NewWidth = floor ( $OrignialWidth - $diff );
			$AdjustDimensions = "T";
		}

		if ($OrignialHeight > $MaximumHeight) {
			$diff = $OrignialHeight - $MaximumWidth;
			$percnt_reduced = (($diff / $OrignialHeight) * 100);
			$NewWidth = floor ( $OrignialWidth - (($percnt_reduced * $OrignialWidth) / 100) );
			$NewHeight = floor ( $OrignialHeight - $diff );
			$AdjustDimensions = "T";
		}

		$thisImageInfo = array ('OriginalImageTag' => $img_tag,'ImageSource' => $ImageSource,'OrignialWidth' => $OrignialWidth,'OrignialHeight' => $OrignialHeight,'NewWidth' => $NewWidth,'NewHeight' => $NewHeight,'AdjustDimensions' => $AdjustDimensions );
		array_push ( $AllImageInfo, $thisImageInfo );
	}

	// build array of before and after tags
	$ImageBeforeAndAfter = array ();
	for($i = 0; $i < count ( $AllImageInfo ); $i ++) {

		if ($AllImageInfo [$i] ['AdjustDimensions'] == "T") {
			$NewImageTag = str_ireplace ( 'width="' . $AllImageInfo [$i] ['OrignialWidth'] . '"', 'width="' . $AllImageInfo [$i] ['NewWidth'] . '"', $AllImageInfo [$i] ['OriginalImageTag'] );
			$NewImageTag = str_ireplace ( 'height="' . $AllImageInfo [$i] ['OrignialHeight'] . '"', 'height="' . $AllImageInfo [$i] ['NewHeight'] . '"', $NewImageTag );

			$thisImageBeforeAndAfter = array ('OriginalImageTag' => $AllImageInfo [$i] ['OriginalImageTag'],'NewImageTag' => $NewImageTag );
			array_push ( $ImageBeforeAndAfter, $thisImageBeforeAndAfter );
		}
	}

	// execute search and replace
	for($i = 0; $i < count ( $ImageBeforeAndAfter ); $i ++) {
		$HTMLContent = str_ireplace ( $ImageBeforeAndAfter [$i] ['OriginalImageTag'], $ImageBeforeAndAfter [$i] ['NewImageTag'], $HTMLContent );
	}

	return $HTMLContent;
}

/*
 * GIBT DIE Statische URL aus (Wird gebrauch fuer das Module - Facebook und für (eventeull) Aufruf der Seite ueber den Adminbereich
 * mm@ssi.at 04.01.2015
 */
function call_static_page_url() {
	$site_id = $_SESSION ['site_id'];
	$query1 = $GLOBALS ['mysqli']->query ( "SELECT * FROM ssi_company.domain WHERE page_id = '{$_SESSION['smart_page_id']}' " );
	$array1 = mysqli_fetch_array ( $query1 );

	$query = $GLOBALS ['mysqli']->query ( "SELECT index_id FROM smart_page WHERE page_id = '{$_SESSION['smart_page_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $query );
	$index_id = $array ['index_id'];

	$query = $GLOBALS ['mysqli']->query ( "SELECT site_url,fk_id FROM smart_langSite WHERE fk_id = '$site_id' AND lang = '{$_SESSION['page_lang']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array = mysqli_fetch_array ( $query );
	$site_url = "/" . $array ['site_url'] . ".html";
	$site_id = $array ['fk_id'];
	if ($index_id == $site_id)
		$site_url = "";

	return "www." . $array1 ['domain'] . "$site_url";
}
function get_verify_form_user() {
	$query_verify = $GLOBALS ['mysqli']->query ( "SELECT verify_key FROM ssi_company.user2company WHERE user_id = '{$_SESSION['user_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
	$array_verify = mysqli_fetch_array ( $query_verify );
	return $array_verify ['verify_key'];
}

// verschiebbare Layer
function show_layer($id, $text, $x = false, $y = false, $h = false, $w = false, $new = false, $layer_fixed = false) {

	/*
	 * Ausgabe eines Layers mit Darstellungsoptionen
	 * @param $id //eindeutige ID des Layers
	 * @param $text //Ausgabetext
	 * @param $x //X - Koordinate
	 * @param $y //Y - Koordinate
	 * @param $h //H - Höhe
	 * @param $w //X - Breite
	 */
	if ($w)
		$set_layer_w = "width:$w" . "px;";
	if ($h)
		$set_layer_h = "height:$h" . "px;";

	if ($y < 0)
		$y = 0;

	// Wenn Layer neuangelegt wird, Änderung des Hintergrundes und des Border
	if ($new)
		$add_style = "z-index: 1004; background-color:white; border:1px dashed red; ";
	if ($layer_fixed)
		$position = 'position:fixed;';
	else
		$position = 'position:absolute;';

	$LayerAdd = "style='$add_style padding:0px; margin:0px; position:absolute; $set_layer_h $set_layer_w top:$y" . "px; left:$x" . "px' ";

	if ($_SESSION ['admin_modus']) {
		$button = "<div class='layer_button ui compact icon buttons'>";
		$button .= "<a href=# class='button_trash icon button ui tooltip' id='trash$id' title='Element löschen'><i class='icon trash'></i></a>";
		$button .= "<a href=# class='button_option icon button ui tooltip' id='$id' title='Einstellungen aufrufen'><i class='icon setting'></i></a>";
		$button .= "<a href=# class='button_move icon button ui tooltip' id='move' title='Element verschieben'><i class='icon move'></i></a>";
		$button .= "</div>";
	}

	if ($new) {
		$ckeditor = "<script>save_content_id('layer_text$id'); </script> ";
	}

	return "
	<div style='width:100%;' class='move_div' id='$id' $LayerAdd >$button
	<div class='cktext' id='layer_text$id' style='$set_layer_h width:100%; overflow: auto; $position' {$_SESSION['add_contenterditble']}> $text</div>
	</div>$ckeditor";
}

?>