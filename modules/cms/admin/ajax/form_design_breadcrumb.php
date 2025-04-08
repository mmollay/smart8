<?php
$sql = $GLOBALS['mysqli']->query ( "SELECT * from smart_layout WHERE page_id = '{$_SESSION['smart_page_id']}'" );
$array_design = mysqli_fetch_array ( $sql );
$layout_id = $array_design['layout_id'];
$layout_array = $array_design['layout_array'];
$layout_array_n = explode ( "|", $layout_array );
foreach ( $layout_array_n as $array ) {
	$array2 = explode ( "=", $array );
	$GLOBALS[$array2[0]] = $array2[1];
}

$arr['field'][] = array ('tab' => $set_tab , 'type' => 'div' , 'class' => 'content  ui message' );
$arr['field']['bread_visible'] = array ('tab' => $set_tab , 'label' => 'Brotkrümmelleiste anzeigen' , 'type' => 'toggle' , 'value' => $bread_visible );
$arr['field'][] = array ('tab' => $set_tab , 'type' => 'div' , 'class' => 'content ui content_breadcrumb' );
$arr['field']['bread_padding_top'] = array ('tab' => $set_tab , 'type' => 'slider' , 'label' => 'Abstand(oben)' , 'min' => 0 , 'max' => 50 , 'step' => 1 , 'unit' => 'px' , 'value' => $bread_padding_top );
$arr['field']['bread_padding_left_right'] = array ('tab' => $set_tab , 'type' => 'slider' , 'label' => 'Abstand(seitlich)' , 'min' => 0 , 'max' => 50 , 'step' => 1 , 'unit' => 'px' , 'value' => $bread_padding_left_right );
$arr['field'][] = array ('tab' => $set_tab , 'type' => 'div_close' );
$arr['field'][] = array ('tab' => $set_tab , 'type' => 'div_close' );