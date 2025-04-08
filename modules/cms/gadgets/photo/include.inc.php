<?php
if ($link) {
	$href = $link;
	// http hinzufügen wenn dieser fehlt
	if ($link && strncasecmp ( 'http', $link, 4 )) {
		$add_http = 'http://';
	}
}

// Default
if (! $explorer) {
	$explorer = "gadgets/images/square-image.png";
}

if (! $size)
	$size = 'large';

else if ($size == 'full') {
	$fullsize = true;
	$size = 'fluid';
}

if ($set_target == 'open_modal') {
	// Seite in Fanybox aufrufen (IFRAME)
	$data_fancybox = "data-type='iframe' data-src='$add_http$href' data-fancybox ";
	// Wenn Bild aufgerufen wird
	$add_link_class = 'modal-video';
} else {
	// Regulären Link aufrufen
	if ($set_target == 'same_tab' or ! $set_target) {
		$set_target = '';
	} else
		$set_target = '_blank';
	$data_fancybox = "href='$add_http$href' $onclick target='$set_target' ";
}

if (! $GLOBALS ['set_image_dimmable']) {
	$add_js2 .= "$('.image-dimmable').dimmer({ on: 'hover' });";
	$GLOBALS ['set_image_dimmable'] = true;
}

// Bei Update
if ($GLOBALS ['set_ajax']) {
	$add_js2 .= "$('.modal-video').fancybox({ youtube : { controls : 0, showinfo : 0 }, vimeo : { color : 'f00' } });";
}

if (! $align)
	$align = 'center';

if ($object_fit) {
	if (! $cover_size_width)
		$cover_size_width = $cover_size;

	$photo_style .= "object-fit: cover; height:$cover_size" . "px; width:$cover_size_width" . "px";
	$size = '';
}

if ($resize or $href or $url) {

	if ($resize) {
		$div_dimmable .= "<a $data_caption href='$explorer' class='ui $variations $size image ' data-fancybox  title='Bild vergrößern'>";
		$div_dimmable_close = "</a>";
	} else {

		if ($url) {
			$div_dimmable .= "<a align='$align' class='ui $variations $size image'  href=\"#\" onclick=\"CallContentSite('$url')\">"; // class='tooltip' title='Aufrufen'
			$div_dimmable_close = "</a>";
		} elseif ($href) {
			$div_dimmable .= "<a align='$align' class='ui tooltip $variations $size image' $data_fancybox>";
			$div_dimmable_close = "</a>";
		}
	}
} else {
	if ($size != 'full') {
		$div_dimmable .= "<div align='$align' class='ui image $variations $size'>";
		$div_dimmable_close = "</div>";
	} else {
		$div_dimmable = "<div class='image'>";
		$div_dimmable_close = "</div>";
	}
}

if ($zoom_effect == 'img_parallax') {
	$img_parallax_script = "<script>var image = document.getElementsByClassName('image$layer_id');new simpleParallax(image,{ delay: .6,});</script>";
	// $img_parallax_script .= "\n<script>$(document).ready(function() { $('.image$layer_id').paroller({ factorXs:'0.1', factor:'0.2', type:'background', direction:'vertical' }); });</script>\n";
} elseif ($zoom_effect) {
	$output_zoom_effect .= "<div class='$zoom_effect'>";
	$output_zoom_effect_close = "</div>";
}

$output .= "$output_zoom_effect$div_dimmable";

if ($image_title) {
	$output .= "<div class='image_title'>$image_title</div>";
}

//if ($_POST ['update_id'])
$output .= "<img style='$photo_style' class='image$layer_id ' src='$explorer'>";

//Lazy- load mode - noch in Entwicklung 
//else $output .= "<img style='$photo_style' class='lazy_load image$layer_id ' src='gadgets/images/square-image.png' data-src='$explorer'>";

$output .= "$div_dimmable_close$output_zoom_effect_close";
$output .= $img_parallax_script;

$variations = '';
$class_image = '';