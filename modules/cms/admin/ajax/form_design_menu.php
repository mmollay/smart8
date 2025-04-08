<?php
$sql = $GLOBALS ['mysqli']->query ( "SELECT * from smart_layout WHERE page_id = '{$_SESSION['smart_page_id']}'" );
$array_design = mysqli_fetch_array ( $sql );
$layout_id = $array_design ['layout_id'];
$layout_array = $array_design ['layout_array'];
$layout_array_n = explode ( "|", $layout_array );
foreach ( $layout_array_n as $array ) {
	$array2 = explode ( "=", $array );
	$GLOBALS [$array2 [0]] = $array2 [1];
}

// Basisdarstellung des Menus
// "no-menu"=>"Menü ausblenden",
$array_menu_layout = array ("sm-default" => "Eigene Darstellung","sm-mint" => "Mint","sm-clean" => "Clean","sm-simple" => "Simple","sm-blue" => "Blue" );
// "sidebar" => "Nur Sidebar mit einem Button"

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'accordion','title' => 'Allgemeine Einstellungen','active' => true );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div','class' => 'ui message' );
// $arr['field']['loginbar'] = array ('tab'=>$set_tab, 'label' => '&nbsp;' , 'label_right' => 'Loginbar aktivieren' , 'type' => 'checkbox' , 'value' => $loginbar );
// $arr['field']['loginbar_color'] = array ('tab'=>$set_tab, 'label' => 'Farbe' , 'type' => 'dropdown' , 'array' => 'color' , 'value' => $loginbar_color );
$arr ['field'] ['menu_fontsize'] = array ('tab' => $set_tab,'label' => 'Schriftgr&ouml;ße','type' => 'slider','min' => 10,'max' => 30,'step' => 1,'unit' => 'px','value' => $menu_fontsize );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div_close' );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'accordion','title' => 'Linkfarben - Ebene 1','split' => true );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div','class' => 'ui message' );
$arr ['field'] ['menu_a_link'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Basis','type' => 'color','value' => $menu_a_link );
// $arr['field'][] = array ('tab'=>$set_tab, 'type' => 'div' , 'class' => 'two fields' );
$arr ['field'] ['menu_a_hover'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'bei Mausbewegung','type' => 'color','value' => $menu_a_hover );
$arr ['field'] ['menu_a_hover_bgcolor'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Hintergrund bei Mausbewegung','type' => 'color','value' => $menu_a_hover_bgcolor );
// $arr['field'][] = array ('tab'=>$set_tab, 'type' => 'div_close' );
$arr ['field'] ['menu_current_color'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Gewählter Button - Textfarbe','type' => 'color','value' => $menu_current_color );
$arr ['field'] ['menu_current_bgcolor'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Gewählter Button - Hintergrund','type' => 'color','value' => $menu_current_bgcolor );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div_close' );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'accordion','title' => 'Linkfarben - Unterebenen','split' => true );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div','class' => 'ui message' );
$arr ['field'] ['menu_ul_a_link'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Basis','type' => 'color','value' => $menu_ul_a_link );
// $arr['field'][] = array ('tab'=>$set_tab, 'type' => 'div' , 'class' => 'two fields' );
$arr ['field'] ['menu_ul_a_hover'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'bei Mausbewegung','type' => 'color','value' => $menu_ul_a_hover );
// $arr['field'][] = array ('tab'=>$set_tab, 'type' => 'div_close' );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div_close' );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'accordion','title' => 'Darstellung','split' => true );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div','class' => 'ui message' );
$arr ['field'] ['menu_field_bg_color'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Hintergrund (hinter Menu)','type' => 'color','value' => $menu_field_bg_color );
$arr ['field'] ['menu_field_bg_image'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Hintergrundbild','type' => explorer,'value' => $menu_field_bg_image );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div','class' => 'two fields' );
$arr ['field'] ['menu_backgroundcolor'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => 'Hintergrund','type' => 'color','value' => $menu_backgroundcolor );
$arr ['field'] ['menu_backgroundcolor2'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'type' => 'color','value' => $menu_backgroundcolor2,'label' => '(Verlauf)' );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div_close' );
$arr ['field'] ['menu_radius'] = array ('tab' => $set_tab,'type' => 'slider','label' => 'Rahmen-Rundung','min' => 0,'max' => 30,'step' => 1,'unit' => 'px','value' => $menu_radius );
$arr ['field'] ['menu_border_size'] = array ('tab' => $set_tab,'type' => 'slider','label' => 'Rahmen-St&auml;rke','min' => 0,'max' => 20,'step' => 1,'unit' => 'px','value' => $menu_border_size );
$arr ['field'] ['menu_border'] = array ('tab' => $set_tab,'label' => 'Rahmen-darstellung','type' => 'dropdown','array' => $array_border,'value' => $menu_border );
$arr ['field'] ['menu_border_color'] = array ('tab' => $set_tab,'label' => 'Rahmen-farbe','type' => 'color','value' => $menu_border_color );
$arr ['field'] ['menu_shadow'] = array ('class_input' => $add_class_design,'tab' => $set_tab,'label' => '&nbsp;','label_right' => 'Schatten','type' => 'checkbox','value' => $menu_shadow );
$arr ['field'] ['menu_seperation_line'] = array ('tab' => $set_tab,'label' => 'Linien zw. Menüpunkten','type' => 'color','value' => $menu_seperation_line );
$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'div_close' );

$arr ['field'] [] = array ('tab' => $set_tab,'type' => 'accordion','close' => true );
?>