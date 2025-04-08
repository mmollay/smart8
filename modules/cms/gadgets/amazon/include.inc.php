<?php
// if ($link) {
// $output = "<font color='$hallo_color'>Hallo</font>";
// }
if ($_SESSION['admin_modus']) {
	$output = "<div class='ui icon message amazon-article'>";
	$output .= "<i class='amazon icon icon_generate_amazon'></i>";
	$output .= "<div class='content'>";
	$output .= "<div class='header'>Erzeuge deinen Amazonartikel</div><br>";
	$output .= "<div class='ui form'><div class='inline fields'>";
	$output .= "<div class='field'><input id='amazon_link' type='text' value ='$amazon_asin' placeholder='B00ILB3EYY'></div>";
	$output .= "<div class='field'><button class='icon ui button' onclick = \"preview_amazon_article('$layer_id',$('#amazon_link').val(),1)\"><i class='icon amazon'></i> Erzeugen</button></div>";
	$output .= "<div class='field'><button class='icon ui button' onclick = \"preview_amazon_article('$layer_id',$('#amazon_link').val(),0)\"><i class='icon search'></i> Vorschau</button></div>";
	$output .= "</div></div>";
	$output .= "</div>";
	$output .= "</div>";
}
