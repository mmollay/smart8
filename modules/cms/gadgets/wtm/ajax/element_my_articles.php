<?php
include ('../mysql.php');

include_once ('../../../gadgets/function.inc.php');
include_once ('../../../gadgets/gallery/include.inc.php');

if ($_SESSION ['set_static'])
	$set_static = true;

$content = '';
$sql = $GLOBALS['mysqli']->query ( "
SELECT gallery_inside,bill_details.art_nr,internet_inside_title,internet_inside_text,date_booking,article_temp.art_title art_title 
 	FROM bills, bill_details, article_temp  
		WHERE bills.bill_id = bill_details.bill_id 
		AND article_temp.art_nr = bill_details.art_nr
		AND client_id = {$_SESSION['client_user_id']}
		GROUP by article_temp.art_nr
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $array = mysqli_fetch_array ( $sql ) ) {
	$gallery = $array ['gallery_inside'];
	
	$output_gallery = '';
	
	// reinigt die Garlerie von Doppel "/"
	$gallery = preg_replace ( "/\/\//", "/", $gallery );
	
	// Call gallery
	if ($gallery) {
		// Pfad umwandeln für die Anzeigen auf der statischen Webseite
		if ($set_static == true) {
			$folder = preg_replace ( "/\/users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $gallery );
		} else {
			$folder = "../../" . $gallery;
		}
		
		// Connect to db faktura
		mysql_select_db ( $cfg_mysql ['db'], $gaSql ['link'] ) or die ( 'Could not select database ' . $cfg_mysql ['db'] );
		
		/*
		 * require_once('../../function.inc.php');
		 *
		 * include("../../gallery/include.inc.php");
		 * $output_gallery = "<br>".$output;
		 */
		// require_once('../../function.inc.php');
		$GLOBALS ['set_ajax'] = true;
		//$output_gallery = "<br>" . call_gallery ( 'collageplus', $folder, 'name' );
	} else
		$output_gallery = '';
	
	if ($set_static == true) {
		$array ['internet_inside_text'] = preg_replace ( "/\/users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $array ['internet_inside_text'] );
	}
	
	if (! $array ['internet_inside_title'])
		$array ['internet_inside_title'] = $array ['art_title'];
	
	if ($array ['date_booking'] != '0000-00-00') {
		//$content .= "<h3><a href='#'>" . $array ['internet_inside_title'] . "</a></h3>" . "<div>" . $array ['internet_inside_text'] . "$output_gallery</div>";
		$content .=
		"<div class='title'><i class='dropdown icon'></i>" . $array ['internet_inside_title'] . "</div>" .
		"<div class='content'>" . $array ['internet_inside_text'] . $output_gallery . "</div>";

	} else {
		//$content .= "<h3><a href='#'>" . $array ['internet_inside_title'] . "</a></h3>" . "<div>" . $strNotJetUnlocked . "</div>";
		$content .= 
		"<div class='title'><i class='dropdown icon'></i>" . $array ['internet_inside_title'] . "</div>" . 
		"<div class='content'>" . "Artikel noch nicht freigegeben" . "</div>";
	}
	$gallery = '';
}

echo "<script> $(function() { $( '#my_products' ).accordion('setting', { onOpen : function(){ $('#modal_article').modal('refresh'); } } ); }); </script>";

if ($content)
	echo "<div style='width:100%' class='ui styled accordion' id='my_products'>$content</div>";
else
	echo "<div align=center><br><br>Keine Artikel vorhanden<br><br></div>";