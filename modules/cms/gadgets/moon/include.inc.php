<?php

// http://codebox.org.uk/pages/html-moon-planet-phases CONFIG -> Moon
$add_css2.= "\n<style type='text/css'>@import 'gadgets/moon/moon.css'; </style>";
$add_path_js .= "\n<script type='text/javascript' src='gadgets/moon/planet_phase.js'></script>";
if ($diameter < 20) $diameter = 100;
$add_js2 .= "
	$(function () {
		$.ajax({
			type: 'POST',
			url: 'gadgets/moon/call_moon.php',
			dataType: 'html',
			data :( {info_position : '$info_position' , color: '$color' , diameter: '$diameter', align : '$align' }),
		 	success: function(data) {  
			 	$('#container_moon').html(data); 
			}
		});
	});";

$output .= "<div id='container_moon'></div>";
