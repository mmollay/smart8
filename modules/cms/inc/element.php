<?php
include_once __DIR__ . '/../php_functions/functions.php';

$set_layer_id = $layer_id = $id;

$sql = $GLOBALS ['mysqli']->query ( "SELECT * from smart_layer WHERE layer_id = '$layer_id'" ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
$array = mysqli_fetch_array ( $sql );
$format = $array ['format'];
// $from_id = $array['from_id'];
$gadget = $array ['gadget'];
$layer_fixed = $array ['layer_fixed'];

// $gadget_array = $gadget_array2 = $array['gadget_array'];
// $gadget_array_n = explode("|", $gadget_array);
// if ($array['gadget_array']) {
// foreach ($gadget_array_n as $array) {
// $array2 = preg_split("[=]", $array, 2);
// ${$array2[0]} = $array2[1];
// }
// }

// New Version off element_options
$gadget_array_n = call_smart_element_option ( $layer_id );
if (is_array ( $gadget_array_n )) {
	foreach ( $gadget_array_n as $key => $value ) {
		${$key} = $value;
	}
}

if (! $gadget)
	$gadget = 'textfield';
// Wird benötigt, damit die Seite als .php gespeichert wird (für Portal zum aufrufen von Detailansicht für Shop
else if ($gadget == 'placeholder') {
	$gadget = 'other';
}

if ($gadget == 'splitter')
	$add_class .= 'splitter_div';
else
	$add_class .= 'textfield_div';

if ($new)
	$GLOBALS ['set_ajax'] = true;

/*
 * Ausgabe eines Textfeldes
 */
if ($_SESSION ['admin_modus']) {

	// Check ist this field visible or not
	$query = $GLOBALS ['mysqli']->query ( "SELECT hidden FROM smart_layer WHERE layer_id = '$id' and hidden = 1 " );
	$hidden = mysqli_num_rows ( $query );
	if ($hidden) {
		$class_icon_hidden = 'hide';
		$style_icon_hidden = '';
		$text_hidden = 'Element ist öffentlich verborgen';
	} else {
		$class_icon_hidden = 'unhide';
		$style_icon_hidden = 'visibility: hidden;';
		$text_hidden = 'Element ist öffentlich sichtbar';
	}

	$hidden_icon = "<i id='icon2_hidden$id' style='font-size:13px; background-color:white; z-index:1; position:absolute; top:2px; right:0px; $style_icon_hidden' class='circular hide icon'></i>"; // edit_modus

	/**
	 * *************************************************
	 * Buttonleiste
	 * *************************************************
	 */
	if ($gadget == 'splitter') {
		$button_bar_class = 'layer_button_splitter';
		// $button_class = 'vertical';
		// $icon_setting = 'columns';
		$icon_setting = 'settings';
		$color_setting = 'orange';
		$dropdown_position = 'top left';
		// $dropdown_position = 'right';
	} else {
		$button_bar_class = 'layer_button_textfield';
		$icon_setting = 'settings';
		$color_setting = 'blue';
		$dropdown_position = 'top right';
	}

	include_once (__DIR__ . "/../lang/" . $_SESSION ['page_lang'] . ".php");

	$str_data_tooltip_title = $str_text ['element'] [$gadget] ['title'];

	// $button_setting = "<a class='icon $color_setting button ui ' id='$id' onclick=call_allotheroption($id,'$gadget') data-tooltip='$str_data_tooltip_title-Einstellungen bearbeiten' ><i class='icon $icon_setting'></i></a>";
	// $item .= "<a class='tooltip icon $color_setting item' id='$id' onclick=call_allotheroption($id,'$gadget') data-position='right center' title='$str_data_tooltip_title-Einstellungen bearbeiten' ><i class='icon $icon_setting'></i>$str_data_tooltip_title-Einstellungen</a>";

	// Settingbutton wird bei Amazon nicht angezeigt, da alle Einstellung direkt im Feld gemacht werden

	// $item .= "<a class='tooltip icon item' id='$id' onclick=call_element_setting($id,'$gadget') data-position='right center' title='Rahmen, Labels und Paramenter' ><i class='icon setting'></i>$str_data_tooltip_title-Einstellungen</a>";

	if ($GLOBALS ['right_div_remove'] or ! $GLOBALS ['right_id']) {
		// if ($gadget != 'splitter')
		$item .= "<a onclick='handle_field(\"clonemove\",$id)' class='tooltip item' data-position='right center' title='Element duplizieren und auf Seite verschieben'><i class='icons'><i class='clone right icon'></i><i class='inverted corner arrow right icon'></i></i>Klonen & Verschieben</a>";
		$item .= "<a onclick='handle_field(\"clone\",$id)' class='tooltip item' data-position='right center' title='Element duplizieren'><i class='icon clone right'></i>Klonen</a>";
		$item .= "<a onclick='handle_field(\"move\",$id)' class='tooltip item' data-position='right center' title='Element auf andere Seite verschieben'><i class='icon toggle right'></i>Verschieben</a>";
		$item .= "<a onclick='handle_field(\"hidden\",$id)' class='tooltip item' data-position='right center' title='$text_hidden' id='text_hidden$id'><i id='icon_hidden$id' class='icon $class_icon_hidden'></i>Online sichtbar</a>";
		$item .= "<div class='divider'></div>";
		$item .= "<a onclick='handle_field(\"archive\",$id)' class='tooltip item' data-position='right center' title='$str_data_tooltip_title entfernen (archivieren)'><i class='icon archive'></i>Archivieren</a>";
		$item .= "<a onclick='handle_field(\"delete\",$id)' class='tooltip item' data-position='right center' title='$str_data_tooltip_title entfernen (endgültig)'><i class='icon red trash'></i>Löschen</a>";
	}

	$button_sub .= "
		<div class='layer_button_dropdown ui $dropdown_position pointing dropdown icon $color_setting button' data-position='$tooltip_position' >
		<i class='caret down icon'></i>
		<div class='menu'>$item</div>
		</div>";

	// $button_sub .="<div class='ui button'>x</div>";
	// $button_sub .="<div class='ui flowing popup top left transition hidden'>";
	// $button_sub .="<div class='ui vertical text menu'>$item</div>";
	// $button_sub .="</div>";

	if (! $gadget)
		$gadget = 'textfield';
	if ($gadget == 'photo') {
		$button_top .= "<a class='icon button ui tooltip' id='$id' onclick=showDialog($id,'$gadget') title='Bild ändern' ><i class='icon photo'></i></a>";
	} elseif ($gadget == 'gallery') {
		// $folder = ltrim ( $folder, '/' ); // entfernt den / am Anfang
		$button_top .= "<a class='icon button ui tooltip' id='$id' onclick=openExplorer('$folder',$id) title='Gallerie bearbeiten' ><i class='icon edit'></i></a>";
	}
	if ($GLOBALS ['right_div_sort'] or ! $GLOBALS ['right_id']) {
		if (! $layer_fixed)
			$button_top .= "<a style='cursor:move' class='button_sort icon button ui tooltip' id='$id' title='Position verschieben'><i class='icon move'></i></a>";
	}

	if ($gadget != 'amazon')
		$button_top .= "<a style='background-color: #d4fb78;' class='tooltip icon button ui' id='$id' onclick=\"call_element_setting($id,'$gadget')\"  title='Einstellungen und Element-Design' ><i class='icon sliders horizontal'></i></a>";

	// $button .= "<a onclick=handle_field('hidden',$id) class='icon button ui' data-tooltip='$text_hidden' id='text_hidden$id'><i id='icon_hidden$id' class='icon $class_icon_hidden'></i></a>";

	// Binde-div damit Tooltip nicht zugeht wenn man mit der Maus die Element-Erweiterung aufruft
	$button = '';
	$button .= "<div id='$id' class='$button_bar_class'>";
	$button .= "<div style='position:absolute; height:13px; width:100px; right:-20px; top:-11px;'></div>";
	$button .= "<div style='position:absolute; height:13px; width:100px; right:-20px; top:28px;'></div>";

	$button .= "<div class='ui compact icon $button_class small buttons'>";

	if ($gadget == 'splitter')
		$button .= $button_sub;

	$button .= $button_top;

	if ($gadget != 'splitter')
		$button .= $button_sub;

	$button .= "</div></div>";
}

/*
 * Aufruf bei GADGET
 */

if ($new) {
	$new_class = 'new_textfield'; // wird benötigt damit die aktuelle ID ausgelesen werden kann
}

if ($hidden_icon or $button) {
	if ($gadget == 'splitter') {
		$buttons = "<div style=''>$hidden_icon$button</div>";
	} else
		$buttons = "<div style='position:absolute; top:0px; right:0px;'>$hidden_icon$button</div>";
}

if ($layer_fixed) {
	$style = 'right: 0; left: 0; margin-right: auto; margin-left: auto; position:fixed; bottom:-12px; z-index:2000;';
	// $style = 'position:fixed; left: 50%; width:600px; margin-left: -300px; bottom:0px; z-index:1000';
	$add_class .= ' smart_content_fix';
}

if ($gadget == 'sitemap2') {
	$gadget = 'site_map';
}
if ($gadget == 'other') {
	$seite_id = $GLOBALS ['seite_id'];
	$set_user_id = $GLOBALS ['set_user_id'];
	// Splitten und Nummer übergeben falls vorhanden ist
	$matches = preg_split ( "/#/", $placeholder );
	$gadget = $matches [0];
} elseif ($gadget == 'youtube') {
	$gadget = 'embed';
} elseif (! $gadget)
	$gadget = 'textfield';

// Alles anderen Gadgets werden direkt über die Includes abgerufen
if (file_exists ( __DIR__ . "/../gadgets/$gadget/include.inc.php" )) {
	include (__DIR__ . "/../gadgets/$gadget/include.inc.php");
}

// echo $gadget."$layer_id<br>";
if (! $output and $gadget != 'textfield' and $gadget != 'menu') {
	$output = "<div class='message ui info'>Gadget nicht definiert</div>";
}

// Bei Neuerstellung CSS und JS direkt anhängen
if ($GLOBALS ['set_ajax']) {
	$output_add .= $add_path_js;
	$add_js2 = preg_replace ( "[<script>|</script>]", "", $add_js2 );
	$output_add .= "<script>$add_js2</script>";
	$output_add .= $add_css2;
} // Default
else {
	$GLOBALS ['add_js2'] .= $add_js2;
	$GLOBALS ['add_path_js'] .= $add_path_js;
	$GLOBALS ['add_css2'] .= $add_css2;
}
// Wenn der Text formatiert wird, dann wird dieser angezeigt (ckeditor)
if ($format) {
	$output = preg_replace ( "/\[\[(.*?)\]\]/", $output, $format );
}

$output = "$output$output_add";

if ($hide_in_smartphone)
	$class_computer_only = ' smart_content_layer';

/* DESIGN */
// //////////////////////////////////////////
if ($segment) {
	if (! $segment_or_message)
		$segment_or_message = 'segment';

	if ($segment_inverted)
		$segment_inverted = 'inverted';

	if ($segment_disabled)
		$segment_disabled = 'disabled';

	if ($segment_compact)
		$segment_compact = 'compact';

	if ($segment_color == 'transparent')
		$segment_color = '';
	elseif ($segment_color) {
		$segment_color = "$segment_color";
	}

	$set_segment = "ui $segment_type $segment_compact $segment_color $segment_inverted  $segment_size $segment_grade $segment_disabled $segment_or_message";
} else {
	// $set_segment = "ui segment basic";
}

// if ($segment_type != 'compact') {
// $align = '';
// }

$segment_inverted = '';
$segment_color = '';
$segment_disabled = '';
$segment_grade = '';
$segment_or_message = '';
$segment_type = '';
// $segment_compact = '';
$segment_size = '';

if ($show_label) {
	if (($label_text or $labelIcon)) {
		if ($label_class == 'right corner' or $label_class == 'left corner')
			$label_text = '';
		if ($labelIcon)
			$label_icon = "<i class='icon $labelIcon'></i>";
		if ($label_link) {
			$label_link_para = 'a';
			$label_link = " href='?site_select=$label_link'";
		} else
			$label_link_para = 'div';

		if (! $label_align)
			$label_align = 'left';

		if (! $label_size)
			$label_size = 'large';

		// if ($label_class == 'right ribbon' or $label_class == 'left ribbon')
		if ($label_size && $label_class == 'top attached') {

			if ($label_size == 'massive')
				$style_label = 'padding-top:64px';
			elseif ($label_size == 'huge')
				$style_label = 'padding-top:50px';
			elseif ($label_size == 'big')
				$style_label = 'padding-top:42px';
			elseif ($label_size == 'large')
				$style_label = 'padding-top:32px';
			elseif ($label_size == 'small')
				$style_label = 'padding-top:28px';
			elseif ($label_size == 'tiny')
				$style_label = 'padding-top:22px';
			elseif ($label_size == 'mini')
				$style_label = 'padding-top:18px';
		}

		if ($label_size && $label_class == 'bottom attached') {

			if ($label_size == 'massive')
				$style_label = 'padding-bottom:60px';
			elseif ($label_size == 'huge')
				$style_label = 'padding-bottom:46px';
			elseif ($label_size == 'big')
				$style_label = 'padding-bottom:38px';
			elseif ($label_size == 'large')
				$style_label = 'padding-bottom:28px';
			elseif ($label_size == 'small')
				$style_label = 'padding-bottom:24px';
			elseif ($label_size == 'tiny')
				$style_label = 'padding-bottom:18px';
			elseif ($label_size == 'mini')
				$style_label = 'padding-bottom:14px';
		}

		$label = "<$label_link_para  $label_link class='ui $label_size $label_color $label_class label' >$label_icon$label_text</$label_link_para>"; // <br><br><span>$label_span</span>
	}
}
// if ($margin_lr)
// $margin_lr = "margin-left:$margin_lr" . "px; margin-right:$margin_lr" . "px;";
$iii ++;

// Parallax - Hintergrund
if ($parallax_show) {

	if ($parallax_mode) {
		$class_parallax = "parallax-image";
	}

	// if ($parallax_filter && ! $segment) {
	if ($parallax_filter) {
		$class_parallax_filter = "parallax_filter";
		$parallax_filter_color = 'ffffff';
		if ($parallax_filter_color) {
			include_once (__DIR__ . '/../admin/function.inc.php');
			$rgba = hex2rgba ( $parallax_filter_color, 0.5 );
			$style_parallax_filter .= "background-color: $rgba; ";
		}
	} elseif (! $segment) {
		$class_parallax_filter = "parallax_no_filter";
	}

	if (! $background_repeat)
		$background_repeat = 'no-repeat';

	if (! $background_size)
		$backgorund_size = 'auto';

	if (! $background_position)
		$background_position = 'center top';

	// if ($parallax_color)
	// $style .= "background-color: $parallax_color; ";

	// Wenn noch kein Bild gewählt wurde, wird DEFAULT mäßig gewählt
	// if ($_SESSION['admin_modus'] && !$parallax_image) {
	// $parallax_image = 'gadgets/images/square-image.png';
	// }

	// $parallax_fixed = 'fixed';

	if ($parallax_color and $parallax_color2)
		$set_background_color_linear = ", linear-gradient($parallax_color, $parallax_color2)";

	$styleParts = [ 'background-color' => $parallax_color,'background-image' => "url($parallax_image) $set_background_color_linear",'background-repeat' => $background_repeat,'background-position' => $background_position,'background-size' => $background_size // Weitere CSS-Eigenschaften hier hinzufügen
	];

	$style = '';

	foreach ( $styleParts as $property => $value ) {
		if (isset ( $value )) {
			$style .= "$property: $value; ";
			$property = '';
		}
	}

	// height:$parallax_height"."px;

	// $add_parallax = 'data-paroller-factor="0.1" data-paroller-factor-sm="0.2" ';

	if ($parallax_mode) {
		// $jquery_parallax .= "\n<script>$(document).ready(function() { $('#sort_$set_layer_id').parally({speed: 0.1, mode: 'background', xpos: '0%', outer: false, offset: 0,}); });</script>\n";
		$jquery_parallax .= "\n<script>$(document).ready(function() { $('.parallax-image').paroller({ factorXs:'0.1', factor:'0.2', type:'background', direction:'vertical' }); });</script>\n";
		// $jquery_parallax .= "\n<script>$(document).ready(function() { $('#sort_$set_layer_id').parallaxie({ size: 'cover',pos_x:'center' , offset: 0.2, }); });</script>\n";
	}

	if ($GLOBALS ['set_ajax'])
		$output_add_parallax = $jquery_parallax;
	else
		$GLOBALS ['add_js2'] .= $jquery_parallax;
} else {
	if (! $segment) {
		if ($gadget == 'textfield')
			$class_parallax_filter = "parallax_no_filter";
		// elseif ($gadget == 'photo' OR $gadget == 'gallery' OR $gadget == 'iframe')
		// $class_parallax_filter = "parallax_no_filter_photo";
		else
			$class_parallax_filter = '';
	}
}

if ($gadget == 'splitter') {
	// $style .= 'margin-bottom:40px; margin-top:10px';
}

if (! $align)
	$align = 'left';

if (! $element_fullsize)
	$fullsize_class = 'smart_content_element';

if (! $gadget != 'splitter') {
	if ($parallax_height && $parallax_height > 50) {
		if ($parallax_height > 0)
			$style_padding .= "max-height:$parallax_height" . "px; overflow:auto; ";
	}
}

// if ($parallax_padding)
if ($element_margin)
	$style_marign .= "margin-top:{$element_margin}px !important; margin-bottom:{$element_margin}px !important;";

if ($element_margin_lr)
	$style_marign .= "margin-left:{$element_margin_lr}px !important; margin-right:{$element_margin_lr}px !important;";

// $style_marign .= "padding-bottom:$element_margin" . "px;";
if ($parallax_padding)
	$style_padding .= "padding-top:{$parallax_padding}px; padding-bottom:{$parallax_padding}px;";

if ($parallax_padding_lr)
	$style_padding .= "padding-left:{$parallax_padding_lr}px; padding-right:{$parallax_padding_lr}px;";

if ($element_width) {
	$style_padding .= "width:{$element_width}%; ";
}

// if ($parallax_padding) {
// $style_padding .= "padding:$parallax_padding" . "px 10px;";
// } else {
// $style_padding .= "padding:10px;";
// }

// $output_margin_div = "<div id='element_margin_$set_layer_id' style='$style_marign'>";

if ($site_id)
	$add_class .= ' element_padding';

if ($_SESSION ['admin_modus'])
	$div_loading_element = "<div class='div_position_absolute_center' id='loading_element$set_layer_id'></div>";

$output = "
	<div style='$style $style_marign' id='sort_$set_layer_id' class='$class_parallax $new_class $add_class segment_field segment_div$iii $set_segment $class_computer_only' $add_parallax >
	<a name='$anker_name'></a>
	$div_loading_element
	$buttons
	<div id='sort_div_$set_layer_id' class='sort_div_field $class_parallax_filter' style='$style_padding $style_parallax_filter '>
	<div class='$fullsize_class' style='text-align:$align; $style_label' ><div id='$set_layer_id'>
	$label
	$output
	</div></div></div>
	</div>
	$output_add_parallax";

$class_parallax_filter = '';
// $output = "$script<div style='$style' class='$add_class $new_class' id='sort_$id'>";
// $output .= $output_design;
// $output .= "</div>";
$style = $add_parallax = $class_parallax = $parallax_show = $add_class = $style_label = '';












