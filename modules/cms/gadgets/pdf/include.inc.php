<?php
$select = 'iframe';

if ($icon)
	$data_icon = "data-icon='$icon' ";

if ($placeholder)
	$data_placeholder = "data-placeholder='$placeholder' ";

if ($aspect_ratio)
	$class = '4:3';

if ($link)
	$data_link = "data-url='$link' ";

if (! $height)
	$height = '400';

	if ($title) 
		$title = " - $title ";
	
	if ($select == 'iframe' && $link) {
	$output .= "
		<object data='$link' type='application/pdf' style='height:".$height."px; width:100%; border:none;'>
        <div class='message ui'>
		<div class='header'>Es sieht so aus als unterstütze der Browser keine Inline-PDFs.</div>
		<div class='content' align=center><br><a class='button ui' href='$link'>Das PDF$title herunterladen</a><br></div>
		</div>
       <embed src='$link' type='application/pdf'>
    </object>";
	//$output .= "<div $style class='ui $class embed' $data_source $data_id $data_placeholder $data_link $data_icon></div>";
	//$output ="<iframe src='$link' style='height:".$height."px; width:100%; border:none;' scrolling='auto'></iframe>";
} else if ($select) {
	$output .= "<div $style class='ui $class embed' $data_source $data_id $data_placeholder $data_link $data_icon></div>";
	
	if (! $GLOBALS['cout_embed']) {
		$add_js2 .= "$(document).ready(function (){ $('.ui.embed').embed(); });";
	}
	
	++ $GLOBALS['cout_embed'];
} else
	$output = "<div class='message ui'><div align=center><br>Feld ist noch nicht definiert<br><br></div></div>";