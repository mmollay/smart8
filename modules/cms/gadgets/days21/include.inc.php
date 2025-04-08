<?php
session_start ();
if ($_SESSION['path21']) $path21 = $_SESSION['path21'];
else  $path21 = "gadgets/days21";

$add_css2 .= "\n<link rel='stylesheet' type='text/css' href='$path21/css/main.css'>";
$add_css2 .= "\n<link rel='stylesheet' type='text/css' href='$path21/css/detail.css'>";

$add_path_js .= "\n<script type='text/javascript' src='$path21/js/jquery.autosize.js'></script>";
$add_path_js .= "\n<script type='text/javascript' src='$path21/js/jquery.imgExplosion/jquery.imgexplosion.js'></script>";
$add_path_js .= "\n<script type='text/javascript' src='$path21/js/ion.sound/ion.sound.min.js'></script>";
//$add_js2 .= "\n<script type='text/javascript' src='$path21/js/jquery.easy-confirm-dialog.js'></script>";
$add_path_js .= "\n<script type='text/javascript' src='$path21/js/main.js'></script>";
//$add_js2 .= "\n<script type='text/javascript' src='$path21/js/detail.js'></script>";
$add_path_js .= "\n<script type='text/javascript' src='$path21/js/admin.js'></script>";

$output .= "<div id='day21_filter'></div>"; // Call the wiht jquery
$output .= "<div id='challenge_list2'></div>";
$output .= "
	<div id='modal_challenge' style='min-height:700px' class='large ui modal'>
	<i class='close icon'></i>
	<div class='header' id=modal_header>Challenge</div>
	<div class='content' id=modal_content></div>
	</div>";
$output .= "<div id=window></div>";

$output .= "
<div class='small ui modal' id='chancel_challenge'><div class='content' id='chancel_modal_content' >Challenge wirklich beenden?</div><div class='actions'>
<div class='ui cancel button'>NEIN</div>
<div class='red ok button ui'>JA</div>
</div></div>";
