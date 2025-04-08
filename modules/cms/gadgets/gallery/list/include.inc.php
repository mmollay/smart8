<?php
/*
 * http://collageplus.edlea.com/
 * like Google +
 * mm@ssi.at 20.06.2013
 */
$GLOBALS['gallery_count'] ++;
if (!$col) $col = 1;
$array_col_word = array ( 1 => "one" , 2 => "two" , 3 => "three" , 4 => "four" );

foreach ( $image_result as $key => $array ) {
	//$text = nl2br($array['text']);
	$text  = $array['text'];
	$title = $array['title'];
	$link  = $array['link'];
	$link_intern = $array['link_intern'];
	
	// Bei Vergrösserung
	if ($after_click == 'resize' or $image_resize) {
		$link1 = "<a data-fancybox='fncbx{$GLOBALS['gallery_count']}' href='{$array['path']}' $set_title>";
		$link2 = "</a>";
	}	// Wenn Link gesetzt ist
	else if ($after_click == 'link' and $link) {
		if (! preg_match ( '[http]', $link )) {
			$link = "http://$link";
		}
		$link1 = "<a href ='{$link}' target ='_blank'>";
		$link2 = "</a>";
		$tooltip = "data-tooltip='Besuche: {$array['link']}' ";
	}	// Wenn interner Link gesetzt ist
	else if ($after_click == 'link' and $link_intern) {
		$link1 = "<a href ='?site_select=$link_intern'>";
		$link2 = "</a>";
	} else {
		// Wenn nichts vorhanden ist
		$tooltip = $link1 = $link2 = '';
	}
	
	
	
// 	if ($after_click == 'resize' or $image_resize) {
// 		$link1_img = "<a class='fancybox' rel='fncbx{$GLOBALS['gallery_count']}' href='{$array['path']}' $set_title>";
// 		$link2_img = "</a>";
// 	} else {
// 		$link1_img = $link2_img = '';
// 	}
	
// 	if ($link) {
// 		if (!preg_match('[http]',$link)) { $link = "http://$link"; }
// 		$link1 = "<a href ='{$link}' target ='_blank'>";
// 		$link2 = "</a>";
// 		if ($after_click == 'link') {
// 			$link1_img = $link1;
// 			$link2_img = $link2;
// 		}
// 	} else if ($link_intern) {	
// 		$link1 = "<a href ='?site_select=$link_intern'>";
// 		$link2 = "</a>";
// 		if ($after_click == 'link') {
// 			$link1_img = $link1;
// 			$link2_img = $link2;
// 		}
// 	} else {
// 		$tooltip = $link1 = $link2 = $link1_img = $link2_img = '';
// 	}
	
	if ($text or $title) {
		//$alt = "alt = '<b>$title</b><br>$text'";
		$alt = "alt = '<b>$title</b>'";
	}
	else 
		$alt = "";
		
	
	++$set_col;
	$list[$set_col] .= "<div class='item'>";
    $list[$set_col] .= "<div class='ui $size image'>";
    $list[$set_col] .= "$link1<img src='{$array['thumb_path']}' $alt >$link2";
   // if ($image_resize) $list[$set_col] .= "</a>";
    $list[$set_col] .= "</div>";
    $list[$set_col] .= "<div class='content'>";
    if ($title) $list[$set_col] .= "<div class='header'>$link1$title$link2</div>";
    $list[$set_col] .= "<div class='smart_content_text'>$text</div>";
  	$list[$set_col] .= "</div></div>";
	
	if ($set_col == $col) {
		$set_col = '';
	}
}

for($ii = 1; $ii <= $col; $ii ++) {
	$set_list .= "<div class='column'><div class='ui items'>{$list[$ii]}</div></div>";
}

$output .= "<div class='ui doubling stackable {$array_col_word[$col]} column grid'>$set_list</div>";
?>