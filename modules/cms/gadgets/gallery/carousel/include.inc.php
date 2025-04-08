<?
/***************************************************************************
 * Include for Carousel (Element)
 * https://owlcarousel2.github.io/OwlCarousel2/demos/basic.html
 * mm@ssi.at 03.11.2017
 ***************************************************************************/
// $rand = md5 ( mt_rand () );
$rand = $layer_id;
// $owl_lazy = true;

/**
 * ****************************************
 * OPTIONS
 * ***************************************
 */

//$option .= 'items: 3,\n';

if ($owl_height >= 10) {
	//if (! $owl_imgheight)
	//	$owl_imgheight = '200';
	
	//$option .= "autoWidth:true,\n";
	//$image_style = "style='height:$owl_imgheight" . "px'";
	$image_style = "style='object-fit: cover; width:auto"."px; height:$owl_height"."px;  ' ";
}

// if ($owl_autoheight)
// 	$option .= "autoHeight:true,\n";

if ($owl_nav) {
	$option .= "nav:true,\n";
	$option .= "navText: [\"zur&uuml;ck\",\"weiter\"],\n";
}
if ($owl_dots)
	$option .= "dots: true,\n";
else
	$option .= "dots: false,\n";

if ($autoload_sec >= 1) {
	$option .= "		autoplay:true,\n";
	$option .= "		autoplayTimeout:$autoload_sec" . "100,\n";
	$option .= "		autoplayHoverPause:true,\n";
	if ($owl_loop) // fängt wieder von vorne an
		$option .= "		loop:true,\n";
}

if ($owl_lazy) {
	$option .= "lazyLoad:true,\n";
}

if (! $owl_item)
	$owl_item = 1;

// Automatische Anzahl der Breite nach auswählen
if ($owl_autocount) {
	$option .= "responsive:{ 0:{ items:1 }, 600:{ items:1 }, 960:{ items:3 }, 1200:{ items:4 }, 1500:{ items:5 } },";
	// Manuelle Anzahl aus geben
} elseif ($owl_item)
	$option .= "		items:$owl_item,";

/**
 * **************
 * OPTIONS - END
 * **************
 */

// height - overflow - cut
// if ($owl_height)
// 	$css_overflow = "style='height:$owl_height" . "px; overflow:hidden'";
	
foreach ( $image_result as $key => $array ) {
	
	if ($show_title) {
		if ($array['title'] or $array['text']) {
			$set_title = "title='$title $text' ";
			$set_title_html = "data-html='$title $text'; class='popup transition visible'; data-position='top center' data-variation='inverted' ";
		} else {
			$set_title = '';
			$set_title_html = "";
		}
	} else {
		$set_title = "";
		$set_title_html = "";
	}
	
	if ($owl_lazy) { // Load small image before
		$add_img = "class='owl-lazy' data-src='{$array['path']}' ";
	} else {
		$add_img = "src='{$array['path']}'";
	}
	
	$link_intern = $array['link_intern'];
	
	if ($array['target'] and !$_SESSION['admin_modus']) $set_target = "target = '_blank' ";
	
	// Bei Vergrösserung
	if ($after_click == 'resize' or $image_resize) {
		$link1 = "<a data-fancybox='fncbx{$GLOBALS['gallery_count']}' href='{$array['path']}' $set_title >";
		$link2 = "</a>";
	} else if ($after_click == 'link' and $link) {
		// Wenn Link gesetzt ist
		if (! preg_match ( '[http]', $link )) {
			$link = "http://$link";
		}
		$link1 = "<a href='{$link}' $set_target>";
		$link2 = "</a>";
		$tooltip = "data-tooltip='Besuche: {$array['link']}' ";
	} else if ($after_click == 'link' and $link_intern) {
		// Wenn interner Link gesetzt ist
		$link1 = "<a href=\"#\" onclick=\"CallContentSite('$link_intern')\" $set_target >"; //href ='?site_select=$link_intern'
		$link2 = "</a>";
	} else {
		// Wenn nichts vorhanden ist
		$tooltip = $link1 = $link2 = '';
	}
	
	if ($array['text']) {
		if ($array['style_align'])
			$array['style'] .= ' float:' . $array['style_align'] . ";";
		
		if ($array['style_width'])
			$array['style'] .= 'max-width:' . $array['style_width'] . ";";
		
		$content_img = "<div class='carousel_message'><div style='padding:5px; background: rgba(255, 255, 255, 0.6); {$array['style']}; '><div class='smart_content_container'>" . $array['text'] . "</div></div></div>";
	} else
		$content_img = "";
	
	$list .= "
	$link1
	<div class='$zoom_effect'>
	$content_img
	<div class='$hover_effect'>
	<img $image_style $add_img title='{$array['title']}' alt='{$array['text']}' $tooltip>
	</div>
	</div>
	$link2
";
}

// Includes for CSS & JS
// wurde direkt in das index integriert
// if (! $GLOBALS['cout_car']) {
// 	$add_path_js .= "\n<script type='text/javascript' src='gadgets/gallery/carousel/owl.carousel.js'></script>";
// 	$add_css2 .= "\n<link rel='stylesheet' href='gadgets/gallery/carousel/assets/owl.carousel.min.css'>";
// 	$add_css2 .= "\n<link rel='stylesheet' href='gadgets/gallery/carousel/assets/owl.theme.default.min.css'>";
// 	// $add_css2 .= "\n<link rel='stylesheet' href='gadgets/gallery/carousel/assets/owl.theme.green.min.css'>";
// }
// ++ $GLOBALS['cout_car'];

// Punktfarbe bestimmen
if ($owl_dots_color)
	$add_css2 .= "
	<style type='text/css'>
	#owl$rand.owl-theme .owl-dots .owl-dot.active span, #owl$rand.owl-theme .owl-dots .owl-dot:hover span { background: $owl_dots_color; }
	#owl$rand.owl-theme .owl-dots .owl-dot span { background: grey; }
	</style>";

$add_css2 .= "<style type='text/css'>div.carousel_message  { z-index:100; position: absolute; width:100%; margin-left: auto; margin-right: auto; left: 0px; bottom: 0px; right: 0px;  }</style>";

if ($owl_width) {
	$add_owl_style = "style='max-width:$owl_width" . "px;'";
}

// Class owl-width verwenden wenn man die Breite etwas eingrenzen will
$output .= "<div align=center><div class='owl-width owl-width$rand' $add_owl_style>";
$output .= "<div id='owl$rand' class='owl-carousel owl-theme'>$list</div>";
$output .= "</div></div>";

// Load - Carousel
$output .= "<script>$(document).ready(function(){ $('#owl$rand').owlCarousel({\n$option animateOut: 'fadeOut', margin:10, }); });</script>";
