<?php
include_once (__DIR__ . '/../../smart_form/include_form.php');

$array_border = array ( "border" => "Gesamt" , "border-top" => "Oben" , "border-bottom" => "Unten" );

// Default-Werte (werden, falls vorhanden danach überschrieben
$content_max_width = 1200;
$menu_padding_a = 11;

$bread_padding_top = 10;
$bread_padding_left_right = 20;
if (! isset ( $header_padding ))
	$header_padding = 1;

// alle moeglich Schriftarten
$array_set_fontFamily = array ( "Verdana" , "Arial" , "Helvetica" , "Century Gothic" , "Georgia" , "Impact" , "Times New Roman" , "Courier New" , "Comic Sans MS" , "BankGothic Md BT" , "Tahoma, Geneva" , "Lucida Sans Unicode, Lucida Grande" , "Lucida Console, Monaco5" , "Trebuchet MS" , "Symbol" );

$array_set_fontGoogleFamily = array ( "Lexend Tera" , "Poppins" , "Montserrat" , "Josefin Sans" , "Lora" , "Playfair Display" , "Caveat" , "Oleo Script" );

foreach ( $array_set_fontFamily as $key => $value ) {
	$value2 = wordwrap ( $value, 3, "\0" );
	$array_fontFamily[$value] = "<div style='font-family:$value'>$value2</div>";
}

foreach ( $array_set_fontGoogleFamily as $key => $value ) {
	$value2 = wordwrap ( $value, 3, "\0" );
	$array_fontGoogleFamily[$value] = "<div style='font-family:$value'>$value2</div>";
}

for($ii = 10; $ii < 20; $ii ++) {
	$array_size[$ii] = $ii . 'px';
}

$sql = $GLOBALS['mysqli']->query ( "SELECT * from smart_layout WHERE page_id = '{$_SESSION['smart_page_id']}'" );
$array_design = mysqli_fetch_array ( $sql );
$layout_id = $array_design['layout_id'];
$layout_array = $array_design['layout_array'];
$layout_array_n = explode ( "|", $layout_array );
foreach ( $layout_array_n as $array ) {
	$array2 = explode ( "=", $array );
	$GLOBALS[$array2[0]] = $array2[1];
}

$style = $array_design['style'];
$jquery = $array_design['jquery'];


$array_background_repeat = array ( 'no-repeat' => '1mal anzeigen' , 'repeat-y' => 'Vertikal wiederholen' , 'repeat-x' => 'Horizontal wiederholen' , 'repeat' => 'kacheln' , 'space' => 'Gekachelt mit Abstand' );
$array_background_size = array ( 'auto' => 'Original' , 'cover' => 'Angepasst' , 'contain' => 'Contain' , '100% auto' => 'Breite 100%' , 'auto 100%' => 'Höhe 100%' );
$array_background_position = array ( 'center top' => 'mittig oben' ,
		'center center' => 'mittig mittig' ,
		'center bottom' => 'mittig unten' ,
		'left top' => 'links oben' ,
		'left center' => 'links mittig' ,
		'left bottom' => 'links unten' ,
		'right top' => 'rechts oben' ,
		'right center' => 'rechts mittig' ,
		'right bottom' => 'rechts unten' );

if (! $header_backgroundrepeat) {
	$header_backgroundrepeat = 'no-repeat';
}

$arr['form'] = array ( 'action' => "admin/ajax/form_design2.php" , 'id' => 'form_design' , 'size' => 'mini' );
$arr['ajax'] = array ( 'success' => "$('#ProzessBarBox').message({ type:'success', title: 'Design gespeichert'}); $('.sidebar-design').sidebar('toggle'); " , 'datatype' => 'html' );

/**
 * *****************************************************************************************
 * ACCORDION: Allgemein
 * ****************************************************************************************
 */

$arr['field']['accordion-design'] = array ( 'type' => 'accordion' , 'title' => 'Schrift' );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['body_fontsize'] = array ( 'label' => 'Schriftgr&ouml;ße' , 'type' => 'slider' , 'min' => 10 , 'max' => 18 , 'step' => 1 , 'unit' => 'px' , 'value' => $body_fontsize );
$arr['field']['body_fonthight'] = array ( 'label' => 'Abstand zwischen der Schrift' , 'type' => 'slider' , 'min' => 1 , 'max' => 20 , 'step' => 1 , 'unit' => '' , 'value' => $body_fonthight , 'value_default' => '5' );
$arr['field']['body_fontcolor'] = array ( 'label' => 'Schriftfarbe' , 'type' => 'color' , 'value' => $body_fontcolor );
$arr['field']['body_fontfamily'] = array ( 'label' => 'Schriftart' , 'type' => 'dropdown' , 'array' => $array_fontFamily , 'value' => $body_fontfamily );
$arr['field']['body_font_googlefamily'] = array ( 'label' => 'Schriftart (Google)' , 'type' => 'dropdown' , 'array' => $array_fontGoogleFamily , 'value' => $body_font_googlefamily , 'clear' => true );
$arr['field']['body_font_google'] = array ( 'tab' => 'basic' ,
		 'label' =>'GoogleFont' ,
		'type' => 'input' ,
		'value' => $body_font_google ,
		'label_right' => "<i class='icon google'></i>" ,
		'label_right_class' => 'button' ,
		'label_right_click' => "window.open('https://fonts.google.com')" ,
		'info' => 'Nutze GoogleFonts - Klicke auf rechts auf den Button und wähle deinen persönlichen Font aus. ' ,
		'placeholder' => 'Open Sans, Lexend Exa, ...' );

$arr['field'][] = array ( 'type' => 'div_close' );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['body_a_link'] = array ( 'label' => 'Linkfarbe' , 'type' => 'color' , 'value' => $body_a_link );
$arr['field']['body_a_hover'] = array ( 'label' => 'bei Mausbewegung' , 'type' => 'color' , 'value' => $body_a_hover );
$arr['field']['body_a_visited'] = array ( 'label' => 'Link (besuchter Link)' , 'type' => 'color' , 'value' => $body_a_visited );
$arr['field'][] = array ( 'type' => 'div_close' );

/**
 * *****************************************************************************************
 * ACCORDION: BACKGROUND
 * ****************************************************************************************
 */
$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'Seite' , 'split' => true );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['body_fixed'] = array ( 'label' => '&nbsp;' , 'label' => 'Hintergrund fixieren' , 'type' => 'checkbox' , 'value' => $body_shadow );
$arr['field']['body_backgroundcolor'] = array ( 'label' => 'Hintergrundfarbe' , 'type' => 'color', 'value' => $body_backgroundcolor );
$arr['field']['body_backgroundimage'] = array ( 'label' => 'Hintergrundbild' , 'type' => 'finder' , 'value' => $body_backgroundimage );
$arr['field']['body_backgroundsize'] = array ( 'type' => 'dropdown' , 'label' => 'Größe' , 'value' => $body_backgroundsize , "array" => $array_background_size , 'value_default' => 'auto' );
$arr['field']['body_backgroundposition'] = array ( 'type' => 'dropdown' , 'label' => 'Ausrichtung' , 'value' => $body_backgroundposition , "array" => $array_background_position , 'value_default' => 'center top' );
$arr['field'][] = array ( 'type' => 'div_close' );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['content_margin_top'] = array ( 'type' => 'slider' , 'label' => "<i class='icon long arrow up'></i> Seite (Oben)" , 'min' => 0 , 'max' => 100 , 'step' => 1 , 'unit' => 'px' , 'value' => $content_margin_top );
$arr['field']['content_width_100'] = array ( 'label' => '&nbsp;' , 'label' => '100% Gesamtbreite' , 'type' => 'toggle' , 'value' => $content_width_100, 'info'=>'Die gesamte Webseite wird auf die ganze Browserbreite angepasst' );
$arr['field']['element_fullsize_all'] = array ( 'label' => '&nbsp;' , 'label' => '100% Breite (Inhalt)' , 'type' => 'toggle' , 'value' => $element_fullsize_all );
$arr['field']['content_max_width'] = array ( 'type' => 'slider' , 'label' => "<i class='icon arrows alternate horizontal'></i> Festgelegte Breite" , 'min' => 600 , 'max' => 1600 , 'step' => 1 , 'unit' => 'px' , 'value' => $content_max_width,'info'=>'Hier wird definiert wie breit die Webseite maximal gezeigt werden soll' );
$arr['field'][] = array ( 'type' => 'div_close' );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'content active ui message show_bodystyle' );
$arr['field']['body_border_on'] = array ( 'label' => '&nbsp;' , 'label' => 'Rahmen' , 'class' => 'hide_border' , 'type' => 'toggle' , 'value' => $body_border_on );
$arr['field'][] = array ( 'type' => 'div' , 'class' => 'show_body_border' );
$arr['field']['body_radius'] = array ( 'type' => 'slider' , 'label' => 'Rundung' , 'min' => 0 , 'max' => 60 , 'step' => 1 , 'unit' => 'px', 'value' => $body_radius );
$arr['field']['body_border_size'] = array ( 'type' => 'slider' , 'label' => 'St&auml;rke' , 'min' => 0 , 'max' => 20 , 'step' => 1 , 'unit' => 'px', 'value' => $body_border_size );
$arr['field']['body_border_color'] = array ( 'label' => 'Linienfarbe' , 'type' => 'color', 'value' => $body_border_color );
$arr['field']['body_shadow'] = array ( 'label' => '&nbsp;' , 'label' => 'Schatten' , 'type' => 'checkbox' , 'value' => $body_shadow );
$arr['field'][] = array ( 'type' => 'div_close' );

$arr['field'][] = array ( 'type' => 'div_close' );


/**
 * *****************************************************************************************
 * ------> BODY
 * ****************************************************************************************
 */

$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'Mittelteil (Erste Ebene)' , 'split' => true );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['middle_backgroundcolor'] = array ( 'label' => 'Hintergrundfarbe' , 'type' => 'color', 'value' => $middle_backgroundcolor );
$arr['field']['middle_backgroundimage'] = array ( 'label' => 'Hintergrundbild' , 'type' => 'finder' , 'value' => $middle_backgroundimage );
$arr['field']['middle_backgroundrepeat'] = array ( 'type' => 'dropdown' , 'label' => 'Wiederholung' , 'value' => $middle_backgroundrepeat , "array" => $array_backgroundimage_repeat );
$arr['field']['middle_padding_top'] = array ( 'type' => 'slider' , 'label' => "<i class='icon long arrow up'></i> Oben" , 'min' => 0 , 'max' => 100 , 'step' => 1 , 'unit' => 'px' , 'value' => $middle_padding_top );
$arr['field']['left_0_div_padding_top'] = array ( 'type' => 'slider' , 'label' => "Elemente <i class='icon arrows alternate vertical'></i>" , 'min' => 0 , 'max' => 90 , 'step' => 1 , 'unit' => 'px', 'value' => $left_0_div_padding_top , 'info' => 'Abstand zwischen den Elementen (oben/unten)' );
$arr['field']['content_padding'] = array ( 'type' => 'slider' , 'label' => "<i class='icon exchange'></i>Element (seitlich)" , 'min' => 0 , 'max' => 200 , 'step' => 1 , 'unit' => 'px', 'value' => $content_padding );
$arr['field']['middle_padding_bottom'] = array ( 'type' => 'slider' , 'label' => "<i class='icon long arrow down'></i> Unten" , 'min' => 0 , 'max' => 100 , 'step' => 1 , 'unit' => 'px' , 'value' => $middle_padding_bottom );
// $arr['field']['left_0_div_padding_left'] = array ( 'type' => 'slider' , 'label' => "Elemente <i class='icon exchange'></i>" , 'min' => 0 , 'max' => 100 , 'step' => 1 , 'unit' => 'px', 'value' => $left_0_div_padding_left , 'info' => 'Abstand zwischen den Elemente (links/rechts)' );

$arr['field'][] = array ( 'type' => 'div_close' );

/**
 * *****************************************************************************************
 * ------> INHALT
 * ****************************************************************************************
 */

$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'Inhalte' , 'split' => true );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'content  ui message' );
$arr['field']['textfield_div_margin'] = array ( 'type' => 'slider' , 'label' => "<i class='icon expand arrows alternate'></i> Elemente (Abstand aussen)" , 'min' => 0 , 'max' => 40 , 'step' => 1 , 'unit' => 'px', 'value' => $textfield_div_margin , 'help' => 'Abstand um die einzelenen Elemente aussen herum' );
//$arr['field']['textfield_div_padding'] = array ( 'type' => 'slider' , 'label' => "<i class='icon compress arrows alternate'></i> Elemente (Abstand innen)" , 'min' => 0 , 'max' => 30 , 'step' => 1 , 'unit' => 'px', 'value' => $textfield_div_padding , 'help' => 'Abstand um die einzelenen Elemente innen herum' );
// $arr['field']['content_element_padding_left_right'] = array ( 'type' => 'slider' , 'label' => "<i class='icon arrows alternate horizontal'></i>Element (zwischen)" , 'min' => 0 , 'max' => 30 , 'step' => 1 , 'unit' => 'px', 'value' => $content_element_padding_left_right );
$arr['field'][] = array ( 'type' => 'div_close' );

// $arr['field'][] = array ( 'type' => 'accordion', 'split' => true, 'title'=>'JQUERY');

// $arr['field'][] = array ( 'type' => 'div' , 'class' => 'content' );
// $arr['field']['jquery'] = array ( 'type' => 'textarea' , 'rows' => 30 , 'value' => $jquery );
// $arr['field'][] = array ( 'type' => 'div_close' );

/**
 * *****************************************************************************************
 * ACCORDION: CSS
 * ****************************************************************************************
 */

$arr['field'][] = array ( 'type' => 'accordion' , 'title' => 'CSS' , 'split' => true );

$arr['field'][] = array ( 'type' => 'div' , 'class' => 'content' );
$arr['field']['style'] = array ( 'type' => 'textarea' , 'rows' => 30 , 'value' => $style );
$arr['field'][] = array ( 'type' => 'div_close' );

$arr['field'][] = array ( 'type' => 'accordion' , 'close' => true );

// if ($GLOBALS['right_edit_layout_menu'] or ! $GLOBALS['right_id']) {
// $arr['field'][] = array ('type' => 'tab' , 'tab' => 'menu_design' );
// include ('form_design_menu.php');
// $arr['field'][] = array ('type' => 'accordion' , 'close' => true );
// }

$arr['field']['component'] = array ( 'type' => 'hidden' , 'value' => $_GET['component'] );
$arr['field']['layout_id'] = array ( 'type' => 'hidden' , 'value' => $layout_id );

// $arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'green' );
// $arr['button']['close'] = array ('class'=>'','value' => 'Einklappen <i class="icon step forward"></i>' , 'color' => 'gray' ,  'js' => "$('.design_sidebar').sidebar('toggle');" );

$output = call_form ( $arr );

$form_design .= $output['html'];
$form_design .= "<a style='right: 258px; top: 0px'  class='hideAll button-designer-close toolbar-left' title='Designer schließen' onclick=\"$('.sidebar-design').sidebar('toggle')\"><i class='icon large green paint brush'></i></a>";
$content_sidebar_admin .= "<div style='display: none;  z-index:1002; ' class='sidebar-design hideAll ui right sidebar segment'><div style='overflow:auto;' class='sitebar_container'>$form_design</div></div>";

$GLOBALS['add_js'] .= $output['js'];
$GLOBALS['add_js'] .= "<script type='text/javascript' src='admin/js/form_design.js'></script>";
?>