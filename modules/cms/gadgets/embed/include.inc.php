<?php
if ($icon)
	$data_icon = "data-icon='$icon' ";

if (! $select) {
	$select = 'youtube';
}

if ($select == 'youtube') {
	if ($autoplay)
		$autoplay = "&autoplay=1";
	else
		$autoplay = '';
	if (! $showinfo)
		$showinfo = "&showinfo=0";
	else
		$showinfo = '';
	
	if (! $rel)
		$rel = 0;
	
	if ($start_time)
		$start_time = "&start=$start_time";
	
	$link = "https://www.youtube.com/embed/$code" . "?rel=$rel$autoplay$showinfo$start_time";
	
	$data_link = "data-url='$link' ";
} elseif ($select == 'vimeo') {
	
	if ($start_time)
		$start_time = "#t=$start_time"."s";
	
	$data_id = "data-id='$code$start_time' ";
	
	$data_source = "data-source='$select' ";
} elseif ($select == 'iframe') {
	if (! preg_match ( "/http/", $link ))
		$link = "http://$link";
	$data_link = "data-url='$link' ";
}

if ($placeholder)
	$data_placeholder = "data-placeholder='$placeholder' ";

if ($aspect_ratio)
	$set_class = '4:3';

// if (! $height)
// $height = '400';

if (($select == 'iframe' and ! $aspect_ratio)) {
	$output = "<iframe src='$link'  style='height:" . $height . "px; width:100%; border:none;' scrolling='auto'></iframe>";
} elseif ($select == 'youtube' and ! $aspect_ratio) {
	$output .= "
	<div class='responsive-video'>
	<iframe src='$link' width='1600' height='900' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
	</div>";
	
	if (! $GLOBALS['count_youtube']) {
		$add_css2 .= "
	<style>
	.responsive-video iframe {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	}
	.responsive-video {
	position: relative;
	padding-bottom: 56.25%; /* Default for 1600x900 videos 16:9 ratio*/
	padding-top: 0px;
	height: 0;
	overflow: hidden;
	}
	</style>";
		$GLOBALS['count_youtube'] ++;
	}
} elseif ($select) {
	$output .= "<div $style class='ui $set_class embed' $data_source $data_id $data_placeholder $data_link $data_icon></div>";
	
	if ($autostart)
		$add_embed .= "'autoplay':'true', ";
	
	if (! $GLOBALS['count_embed']) {
		$add_js2 .= "$(document).ready(function (){ $('.ui.embed').embed({" . $add_embed . "}); });";
	}
	
	++ $GLOBALS['count_embed'];
} else
	$output = "<div class='message ui'><div align=center><br>'Einbettung' ist noch nicht definiert<br><br></div></div>";