<?php
if ($_POST['ajax']) {
	include_once ('../../config.inc.php');
}

include_once ('../config.inc.php');

$set_ajax = 'true';
require_once ('call_groups.php');
require_once ('call_articles.php');

$set_ajax = 'false';

$content = "<script> 
$(document).ready( function() {
	$('#sitemap_dropdown').dropdown();
";

// macht Warenkorb zu einem Dialogfenster
if ($cart_place == 'inner_panel') {
	$content .= "
	$('#button_cart_open,.add_cart').click( function(){
		$('#cart').dialog({'title':'$strCartTitleCart', height:'auto', maxHeight: '400px'});
		if (!$('#cart').val()) {
			$.ajax({
				url: '{$relative_path}cart/call_cart.php', 
				data: ({ajax : true}),
				//beforeSend: function(){ $('#cart').html('<br><br><div align=center>...wird geladen</div><br><br>'); },
				success: function(data) { $('#cart').html(data); },
				type: 'POST'
			});
		}	
	});
";
}

$content .= "}); </script>";

if ($msg_Paypal)
	$products = "<br><br><br><div align=center>$msg_Paypal</div>";

if (! $article_style) {
	
	if ($show['cart']) {
		$content .= "<br><div class='ui stackable grid'>";
		$content .= "<div class='ten wide column'>";
	}
	$content .= "<div id=content_cart class=content_cart>";
	$content .= "$content_groups";
	$content .= "<div id=portal_products>$products</div>";
	
	if ($show['cart']) {
		$content .= "</div>";
		$content .= "</div>";
	}
	
	// if (!$cart_place and $show['cart']) {
	if ($show['cart']) {
		$content .= "<div class='six wide column'>";
		$content .= "<div class='segment mini ui'><h4 class='ui block header'>$strCartTitleCart</h4><div id=cart >$content_cart</div></div>";
		$content .= "</div>";
	$content .= "</div>";
	}
	
}

if ($cart_place == 'inner_panel') {
	$content .= "<div id=cart></div>";
}

if ($_POST['ajax']) {
	echo $content;
}