<?php
$finder_version = 2; // 1 = old Version

// Wenn auf die neue Version vom Finder umgestellt werden soll, dann wird statt call_finder_v1 call_finder verwendet
if ($finder_version == 1)
	$value_call_finder = "call_finder_v1";
else
	$value_call_finder = "call_finder_v2";

$text_placeholder = 'Dateipfad';
$text_choose_file = 'Datei wählen';

$type_field = "
<div class='ui fluid action input'>
<input class='$form_id $class_input ui-input' placeholder='$text_placeholder' type='text' name ='$id' id='$id' value='$value'>
<div class='tooltip ui icon button mini load_img' onclick=\"$value_call_finder('{$arr['hidden']['update_id']}','$id')\" title='$text_choose_file' ><i class='icon upload'></i></div>
</div>";

$flyout_title = $flyout ['title'] ?? 'Finder smart';
$flyout_content = $flyout ['content'] ?? 'Conten';
$flyout_class = $flyout ['class'] ?? 'fullscreen';
$flyout_content = "<iframe src='../file_manager.php' frameBorder='0' scrolling='auto' width=100% height=100% onload='resizeIframe(this)'></iframe>";

// Erzeugt einen flyout oder modal
if (! $set_finder) {
	$set_modul = 'flyout';
	// $modul_arr ['close_hide'] = true;
	// $modul_arr ['url'];
	// $modul_arr ['button'];
	// $modul_arr ['id'];
	$modul_arr ['flyout_finder'] ['title'] = $flyout_title;
	$modul_arr ['flyout_finder'] ['content'] = $flyout_content;
	$modul_arr ['flyout_finder'] ['class'] = $flyout_class;

	$output_flyout_modal = generate_element ( $modul_arr, 'flyout' );
	$add_js_finder = "function resizeIframe(obj){ obj.style.height = 0; obj.style.height = $(window).height() - 100 + 'px'; }";
}

$set_finder = true;
