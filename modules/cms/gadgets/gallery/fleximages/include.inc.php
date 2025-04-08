<?php
session_start ();
/*
 * http://collageplus.edlea.com/
 * like Google +
 * mm@ssi.at 20.06.2013
 */

// Wird Defaultmässig geladen
// if (! $GLOBALS['set_flex_images']) {
// $add_css2 = "\n<link rel='stylesheet' href='gadgets/gallery/fleximages/jquery.flex-images.css'>";
// $add_path_js = "\n<script src='gadgets/gallery/fleximages/jquery.flex-images.min.js'></script>";

if (! $rowHeight)
	$rowHeight = 150;

$rand = md5 ( mt_rand () );
// $output .= "<div class='ui segment load-flex-images'><div class='ui active inverted dimmer'><div class='ui text loader'>Galerie wird geladen</div></div><p></p><br><br><br></div>";
$output .= "<div class='flex-images' id='flex-images$layer_id'>";

foreach ( $image_result as $key => $array ) {
	$title = "<b>" . $array['title'] . "</b>";
	$title_main = $array['title'];
	$text = "<br>" . $array['text'];
	$link = $array['link'];
	$link_intern = $array['link_intern'];
	
	if ($array['title'] or $array['text']) {
		$set_title = "data-caption='$title $text' ";
		$set_title_html = "data-caption='$title $text'  data-html='$title $text'; class='popup transition visible'; data-position='top center' data-variation='inverted' ";
	} else {
		$set_title = '';
		$set_title_html = "";
	}
	
	// <div class='item' data-w='{$array['thumb_width']}' data-h='{$array['thumb_height']}' >
	// <div class='item' data-w='150' data-h='140' >
	// Inline Title
	
	// Bei Vergrösserung
	if ($after_click == 'resize' or $image_resize) {
		$link1 = "<a data-fancybox='fncbx{$GLOBALS['gallery_count']}' href='{$array['path']}' $set_title >";
		$link2 = "</a>";
	} else if ($after_click == 'link' and $link) {
		// Wenn Link gesetzt ist
		if (! preg_match ( '[http]', $link )) {
			$link = "http://$link";
		}
		$link1 = "<a href ='{$link}' target ='_blank'>";
		$link2 = "</a>";
		$tooltip = "data-tooltip='Besuche: {$array['link']}' ";
	} else if ($after_click == 'link' and $link_intern) {
		// Wenn interner Link gesetzt ist
		$link1 = "<a href ='?site_select=$link_intern'>";
		$link2 = "</a>";
	} else {
		// Wenn nichts vorhanden ist
		$tooltip = $link1 = $link2 = '';
	}
	
	$output .= "$link1<div class='item' data-w='{$array['thumb_width']}' data-h='{$array['thumb_height']}' >
	<img $tooltip data-src='{$array['thumb_path']}' src='{$array['thumb_path']}' $set_title_html  >
	<div class='over'>$title_main</div>
	</div>$link2";
	
	if ($image_resize)
		$output .= "</a>";
}
$output .= "</div>";

$output .= "\n
	<script>
	$(document).ready(function()
			{
			//$('.load-flex-images').hide();
			$('#flex-images$layer_id').show();
			$('#flex-images$layer_id').flexImages({rowHeight: $rowHeight});
			$('.popup').popup();
	});
	</script>";

?>