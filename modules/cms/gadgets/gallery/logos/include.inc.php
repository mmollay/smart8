<?php
$rand = md5 ( mt_rand () );
/*
 * für Logos mit max. Größe Breite Feld
 * mm@ssi.at 22.07.2016
 */
foreach ( $image_result as $key => $array ) {
	// $text = nl2br($array['text']);
	$text = $array['text'];
	$title = $array['title'];
	$link = $array['link'];
	$link_intern = $array['link_intern'];
	
	
	if ($text or $title) {
		// $alt = "alt = '<b>$title</b><br>$text'";
		$alt = "alt = '<b>$title</b>'";
	}
	
	//Bei Vergrösserung
	if ($after_click == 'resize' or $image_resize) {
		$link1 = "<a data-fancybox='fncbx$rand' href='{$array['path']}' $set_title>";
		$link2 = "</a>";
	} 
	//Wenn Link gesetzt ist
	else if ($after_click == 'link' and $link) {
		if (! preg_match ( '[http]', $link )) {
			$link = "http://$link";
		}
		$link1 = "<a href ='{$link}' target ='_blank'>";
		$link2 = "</a>";
		$tooltip = "data-tooltip='Besuche: {$array['link']}' ";
		
	} 
	//Wenn interner Link gesetzt ist
	else if ($after_click == 'link' and $link_intern) {
		$link1 = "<a href ='?site_select=$link_intern'>";
		$link2 = "</a>";
	} else {
	//Wenn nichts vorhanden ist
		$tooltip = $link1 = $link2 = '';
	}
	
	if ($text or $title) {
		// $alt = "alt = '<b>$title</b><br>$text'";
		$alt = "alt = '<b>$title</b>'";
	}
	
	$list .= "<div $tooltip >$link1<img data-src='{$array['thumb_path']}' src='{$array['thumb_path']}' style='max-width:100%; $gallery_style' >$link2</div>";
	
	if ($image_resize)
		$list .= "</a>";
}

$output .= "<div align=center>$list</div>";
?>