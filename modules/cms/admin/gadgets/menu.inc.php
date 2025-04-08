<?
$onLoad .= "
call_form_design_menu();

if ($('#dropdown_menu_version').dropdown('get value') =='classic') { 
	$('#config_classic').show();
	$('#config_semantic').hide();
}else {
	$('#config_classic').hide();
	$('#config_semantic').show();
}

$('#dropdown_menu_version').dropdown({ 
	onChange : function(value) { 
		if ($('#dropdown_menu_version').dropdown('get value')=='classic') {
			$('#config_classic').show();
			$('#config_semantic').hide();
			
		} 
		else { 
			$('#config_classic').hide();
			$('#config_semantic').show(); 
		} 
	}
});
";

if (! $menu_version) {
	$menu_version = 'semantic';
}

$array_menu_count_item = array ( 'auto' => 'automatisch' , 'one' => '1' , 'two' => 2 , 'three' => 3 , 'four' => 4 , 'five' => 5 );

$array_menu_count_item = array ( '0' => 'automatisch' , '1' => '+1' , '2' => '+2' , '3' => '+3' , '4' => '+4' );

$array_view = array ( 'classic' => 'Klassisch' , 'semantic' => 'Semantic' );

$array_design = array ( '' => 'Standard' , 'pointing' => 'Pointing' , 'secondary' => 'Rahmenfrei' , 'secondary pointing' => 'Unterstrichen' , 'tabular' => 'Tabulator' , 'text' => 'Text' );

$arr['field']["menu_version"] = array ( 'tab' => 'first' , 'label' => 'Darstellung' , 'type' => 'dropdown' , 'array' => $array_view , 'value' => $menu_version , 'placeholder' => 'Darstellung' );

$arr['field']['config_semantic'] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Einstellungen' );

// Config - Semantic-Menu
$arr['field']["menu_design"] = array ( 'tab' => 'first' , 'label' => 'Art' , 'type' => 'dropdown' , 'array' => $array_design , 'value' => $menu_design , 'placeholder' => 'Darstellung' );
$arr['field']['menu_size'] = array ( 'tab' => 'first' , 'label' => 'Größe' , 'type' => 'dropdown' , "array" => $array_size , 'value' => $menu_size );

$arr['field']["menu_color"] = array ( 'tab' => 'first' , 'label' => 'Farbe' , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $menu_color , 'placeholder' => 'Farben' );
$arr['field']["menu_color2"] = array ( 'tab' => 'first' , 'label' => 'Farbe' , 'type' => 'color' , 'array' => 'color' , 'value' => $menu_color2  );
$arr['field']["menu_color_all"] = array ( 'tab' => 'first' , 'label' => 'alle Links einfärben' , 'type' => 'checkbox' , 'value' => $menu_color_all );
$arr['field']["menu_inverted"] = array ( 'tab' => 'first' , 'label' => 'Invertieren' , 'type' => 'checkbox' , 'value' => $menu_inverted );

$arr['field']["menu_stretch"] = array ( 'tab' => 'first' , 'label' => 'auf Breite ausdehnen' , 'type' => 'checkbox' , 'value' => $menu_stretch );
$arr['field']["menu_count_item"] = array ( 'tab' => 'first' , 'type' => 'dropdown' , 'value' => $menu_count_item , 'array' => $array_menu_count_item );

// $arr['field']["menu_fixed"] = array ( 'tab' => 'first' , 'label' => 'Fixiert' , 'type' => 'checkbox' , 'value' => $menu_fixed );
$arr['field']["menu_compact"] = array ( 'tab' => 'first' , 'label' => 'Kompakt' , 'type' => 'checkbox' , 'value' => $menu_compact );
$arr['field']["menu_borderless"] = array ( 'tab' => 'first' , 'label' => 'Ohne Rahmen' , 'type' => 'checkbox' , 'value' => $menu_borderless );

$arr['field']["menu_vertical"] = array ( 'tab' => 'first' , 'label' => 'Vertikal ausrichten' , 'type' => 'checkbox' , 'value' => $menu_vertical );
$arr['field']["menu_fluid"] = array ( 'tab' => 'first' , 'label' => 'Gestreckt' , 'type' => 'checkbox' , 'value' => $menu_fluid );
$arr['field']["menu_attached"] = array ( 'tab' => 'first' , 'label' => 'Angehängt' , 'type' => 'checkbox' , 'value' => $menu_attached );


$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'close' => true );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

// Config - Classic
$arr['field']['config_classic'] = array ( 'tab' => 'first' , 'type' => 'div' );

/**
 * ********************************************************
 * MENÜEINSTELLUNGEN
 * Smart-Menü
 * *******************************************************
 */
// Damit wird verhindert, dass das Element neu geladen wird
$add_class_design = 'no_auto_save';

// $sql = $GLOBALS['mysqli']->query ( "SELECT * from smart_layout WHERE page_id = '{$_SESSION['smart_page_id']}'" );
// $array_design = mysqli_fetch_array ( $sql );
// $layout_id = $array_design['layout_id'];
// $layout_array = $array_design['layout_array'];
// $layout_array_n = explode ( "|", $layout_array );
// foreach ( $layout_array_n as $array ) {
// $array2 = explode ( "=", $array );
// $GLOBALS[$array2[0]] = $array2[1];
// }

// Basisdarstellung des Menus
$array_menu_layout = array ( "sm-default" => "Eigene Darstellung" , "sm-mint" => "Mint" , "sm-clean" => "Clean" , "sm-simple" => "Simple" , "sm-blue" => "Blue" );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Allgemeine Einstellungen' , 'active' =>true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['menu_fontsize'] = array ( 'tab' => 'first' , 'label' => 'Schriftgr&ouml;ße' , 'type' => 'slider' , 'min' => 10 , 'max' => 30 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_fontsize );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Linkfarben - Ebene 1' , 'split' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['menu_a_link'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Basis' , 'type' => 'color', 'value' => $menu_a_link );
// $arr['field'][] = array ('tab'=>first, 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['menu_a_hover'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'bei Mausbewegung' , 'type' => 'color', 'value' => $menu_a_hover );
$arr['field']['menu_a_hover_bgcolor'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Hintergrund bei Mausbewegung' , 'type' => 'color', 'value' => $menu_a_hover_bgcolor );
// $arr['field'][] = array ('tab'=>first, 'type' => 'div_close' );
$arr['field']['menu_current_color'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Gewählter Button - Textfarbe' , 'type' => 'color', 'value' => $menu_current_color );
$arr['field']['menu_current_bgcolor'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Gewählter Button - Hintergrund' , 'type' => 'color', 'value' => $menu_current_bgcolor );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Linkfarben - Unterebenen' , 'split' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['menu_ul_a_link'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Basis' , 'type' => 'color', 'value' => $menu_ul_a_link );
// $arr['field'][] = array ('tab'=>first, 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['menu_ul_a_hover'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'bei Mausbewegung' , 'type' => 'color', 'value' => $menu_ul_a_hover );
// $arr['field'][] = array ('tab'=>first, 'type' => 'div_close' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Darstellung' , 'split' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['menu_field_bg_color'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Hintergrund (hinter Menu)' , 'type' => 'color', 'value' => $menu_field_bg_color );
$arr['field']['menu_field_bg_image'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Hintergrundbild' , 'type' => 'finder' , 'value' => $menu_field_bg_image );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'two fields' );
$arr['field']['menu_backgroundcolor'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => 'Hintergrund' , 'type' => 'color', 'value' => $menu_backgroundcolor );
$arr['field']['menu_backgroundcolor2'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'type' => 'color', 'value' => $menu_backgroundcolor2 , 'label' => '(Verlauf)' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );
$arr['field']['menu_radius'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Rahmen-Rundung' , 'min' => 0 , 'max' => 30 , 'step' => 1 , 'unit' => 'px', 'value' => $menu_radius );
$arr['field']['menu_border_size'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Rahmen-St&auml;rke' , 'min' => 0 , 'max' => 20 , 'step' => 1 , 'unit' => 'px', 'value' => $menu_border_size );
$arr['field']['menu_border'] = array ( 'tab' => 'first' , 'label' => 'Rahmen-darstellung' , 'type' => 'dropdown' , 'array' => $array_border , 'value' => $menu_border );
$arr['field']['menu_border_color'] = array ( 'tab' => 'first' , 'label' => 'Rahmen-farbe' , 'type' => 'color', 'value' => $menu_border_color );
$arr['field']['menu_shadow'] = array ( 'class_input' => $add_class_design , 'tab' => 'first' , 'label' => '&nbsp;' ,  'label_right' => 'Schatten' , 'type' => 'checkbox' , 'value' => $menu_shadow );
$arr['field']['menu_seperation_line'] = array ( 'tab' => 'first' , 'label' => 'Linien zw. Menüpunkten' , 'type' => 'color', 'value' => $menu_seperation_line );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'close' => true );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Abstände' );

// $arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div' , 'class' => 'ui message' );
$arr['field']['menu_padding_top'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Abstand (oben)' , 'min' => 0 , 'max' => 50 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_top );
$arr['field']['menu_padding_bottom'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Abstand (unten)' , 'min' => 0 , 'max' => 50 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_bottom );
$arr['field']['menu_padding_left_right'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Abstand(seitlich)' , 'min' => 0 , 'max' => 50 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_left_right );
$arr['field']['menu_padding_left'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Abstand(links)' , 'min' => 0 , 'max' => 500 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_left );
$arr['field']['menu_padding_right'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Abstand(rechts)' , 'min' => 0 , 'max' => 500 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_right );
// $arr['field']['menu_padding_a'] = array ('tab'=>first, 'type' => 'slider' , 'label' => 'H&ouml;he Button' , 'min' => 0 , 'max' => 20 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_padding_a );
$arr['field']['menu_button_padding_left_right'] = array ( 'tab' => 'first' , 'type' => 'slider' , 'label' => 'Button (seitlich)' , 'min' => 0 , 'max' => 80 , 'step' => 1 , 'unit' => 'px' , 'value' => $menu_button_padding_left_right );
//
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'close' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'title' => 'Smart-Phone Menü' );
$arr['field']['phone_nav_bg_color'] = array ( 'tab' => 'first' , 'label' => 'Hintergrund' , 'type' => 'color', 'value' => $phone_nav_bg_color );
$arr['field']['phone_nav_font_color'] = array ( 'tab' => 'first' , 'label' => 'Schriftfarbe' , 'type' => 'color', 'value' => $phone_nav_font_color );
$arr['field']["phone_nav_on_top"] = array ( 'tab' => 'first' , 'label' => 'Oben anhängen' , 'type' => 'checkbox' , 'value' => $phone_nav_on_top , 'info' => 'In der Smart-Phonedarstellung wird das Menü oben angezeigt.' );
$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'accordion' , 'close' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'type' => 'div_close' );

$array_menu_text_transform = array ( '' => 'Normal' , 'uppercase' => 'GROSSSCHREIBUNG' , 'lowercase' => 'kleinschreibung' );

$arr['field']["menu_text_transform"] = array ( 'tab' => 'first' , 'label' => 'Groß/Kleinschreibung' , 'type' => 'dropdown' , 'value' => $menu_text_transform , 'array' => $array_menu_text_transform );
