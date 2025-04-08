<?php
/*
 * Erstell am 22.10.2017
 * mm@ssi.at Martin
 * Erzeugt einen Splitter und Elemente für die Darstellung von Amazon + Images
 */
include_once ('../../../../login/config_main.inc.php');
include ('../../function_generate_element_template.php');

$amazon_specification = $_POST[amazon_specification];
$amazon_title = "<br><br><br><div style='text-align:center; color:white'><h1>" . $_POST[amazon_title] . "</h1></div><br><br><br><br><br>";
$amazon_bullets = $_POST[amazon_bullets];
$amazon_price = $_POST[amazon_price];
$amazon_pic = $_POST[amazon_pic];
$amazon_pic_gallery = $_POST[amazon_pic_gallery];
$amazon_id = $_POST[amazon_id];
$amazon_description = $_POST[amazon_description];

$amazon_description = preg_replace ( '#<script(.*?)>(.*?)</script>#is', '', $amazon_description );
$amazon_description = str_replace ( '<p>&nbsp;</p>', '', $amazon_description );

// https://images-na.ssl-images-amazon.com/images/I/41TD5DwfuSL._SX38_SY50_CR,0,0,38,50_.jpg
// https://images-na.ssl-images-amazon.com/images/I/41TD5DwfuSL._SX400_SY600_CR,0,0,1200,400_.jpg
// https://images-na.ssl-images-amazon.com/images/I/41TD5DwfuSL._SX800_SY800_CR,0,0,1200,600_.jpg
/**
 * ***********************************************************
 * Liest src aus - für Gallery und Klassische Bilddarstellung
 * ***********************************************************
 */
include_once ('simple_html_dom.php');

exec ( "mkdir {$_SESSION['HTTP_SERVER_FOLDER_DEFAULT']}amazon/" );
exec ( "mkdir {$_SESSION['HTTP_SERVER_FOLDER_DEFAULT']}amazon/$amazon_id" );

// Auslesen der Gallery
if ($amazon_pic_gallery) {
	$html = str_get_html ( $amazon_pic_gallery );
	// Find all images
	foreach ( $html->find ( 'img' ) as $element ) {
		$get_src = $element->src;
		if (! preg_match ( '/transparent-pixel|en_US/', $get_src )) {
			$explode = explode ( '._', $get_src );
			$ii ++;
			$array_src[$ii] = $explode['0'] . "._S600_.jpg";
			$array_src_head[$ii] = $explode['0'] . "._SX800_SY800_CR,0,0,1200,600_.jpg";
			// echo "<img src=$src>$src<br>";
			$newfile = "{$_SESSION['HTTP_SERVER_FOLDER_DEFAULT']}amazon/$amazon_id/" . basename ( $array_src[$ii] );
			// Dateien werden auf den Rechner gespielt
			if (copy ( $array_src[$ii], $newfile )) {
				echo "Copy success!";
			} else {
				echo "Copy failed.";
			}
		}
	}
}

$src_head = $array_src_head[1];
// $src_head = "/smart_users/ssi/user40/explorer/13/1932299_727343890623104_1153650400_n.jpg";

$site_id = $_SESSION['site_id'];
$layer_id = $mysqli->real_escape_string ( $_POST[layer_id] );

// Diese Felder (layer) werden in die Seite der Reihenfolge nach übertragen

// Head
$generate_array['splitter1'] = array ( 'position' => 'left' , 'gadget' => 'splitter' , 'gadget_array' => "column_relation=1|cell_design=empty|parallax_image=$src_head|parallax_mode=1|parallax_show=1|parallax_filter=1" , layer_id => $layer_id );

$generate_array['amazon_gallery'] = array ( 'position' => 'left' , 'gadget' => 'gallery' , 'gadget_array' => "folder=/amazon/$amazon_id|after_click=resize|" );
// Details
$generate_array['splitter2'] = array ( 'position' => 'left' , 'gadget' => 'splitter' , 'gadget_array' => 'column_relation=8|cell_design=empty|' );

$generate_array['splitter3'] = array ( 'position' => 'left' , 'gadget' => 'splitter' , 'gadget_array' => 'column_relation=8|cell_design=empty|' );

$generate_array['amazon_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter1'  );
$generate_array['amazon_button'] = array ( 'position' => 'left' , 'gadget' => 'button' , 'gadget_array' => 'no_fluid=1|button_size=large|' , 'splitter_layer_id' => 'splitter1' , 'array_button' => array ( 'title' => 'RABATTCODE JETZT SICHERN' , 'color' => 'orange' , 'icon' => 'star' ) );
$generate_array['amazon_bullets'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter2' );
$generate_array["amazon_image1"] = array ( 'position' => 'right' , 'gadget' => 'photo' , 'gadget_array' => "variations=rounded|explorer=$array_src[1]|resize=1|size=medium|" , 'splitter_layer_id' => 'splitter2' );
$generate_array["amazon_image3"] = array ( 'position' => 'left' , 'gadget' => 'photo' , 'gadget_array' => "variations=rounded|explorer=$array_src[3]|resize=1|size=medium|" , 'splitter_layer_id' => 'splitter2' );
$generate_array['amazon_description'] = array ( 'position' => 'right' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter2' );
$generate_array['amazon_specification'] = array ( 'position' => 'right' , 'splitter_layer_id' => 'splitter2', 'gadget'=>'textfield' );
$generate_array['amazon_price'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter2' );
$generate_array["amazon_image2"] = array ( 'position' => 'left' , 'gadget' => 'photo' , 'gadget_array' => "variations=rounded|explorer=$array_src[2]|resize=1|size=medium|" , 'splitter_layer_id' => 'splitter3' );

// foreach ( $array_src as $key => $src ) {
// $generate_array["amazon_image$ii"] = array ( 'position' => 'right' , 'gadget' => 'photo' , 'gadget_array' => "variations=rounded|explorer=$src|resize=1|size=medium|" );
// }

generate_element_template ( $site_id, $generate_array );

echo "ok";