<?php
include ('../inc/data_call.php');

$map_zip = $_SESSION["map_filter"]['map_zip'];
$map_places = $_SESSION["map_filter"]['map_places'];
$map_not_defined = $_SESSION["map_filter"]['not_defined'];
$map_set_admin = $_SESSION["map_filter"]['set_admin'];
$map_autofit = $_SESSION["map_filter"]['autofit'];

if ($_SESSION['admin_modus'])
	$set_path = '../ssi_smart/';

include_once (__DIR__ . "/../../../smart_form/include_form.php");
$arr['form'] = array ( 'type' => 'script' , 'id' => "fruit_map_filter" , 'action' => "{$set_path}gadgets/map/ajax/set_session.php" );
// $arr['form'] = array ( 'type' => 'script' , 'id' => "fruit_map_filter" , 'action' => '../ssi_smart/gadgets/map/ajax/set_session.php' );
if (! $map_zip)
	$map_zip = 'all';

if (! $map_places)
	$map_places = 'all';

	
// Anzahl der Bäume angeben
foreach ( $array_city as $key => $value ) {
	if ($key != 'all') {
		$sql_add = "AND zip = '$key' ";
	} else
		$sql_add = '';
	
	$query = $GLOBALS['mysqli']->query ( "SELECT COUNT(*) from tree WHERE 1 $sql_add AND tree.trash = '0' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array_count_country = mysqli_fetch_array ( $query );
	$array_city2[$key] = $array_city[$key] . " (" . $array_count_country[0] . ")";
}

// if ($_SESSION['admin_modus']) {
// 	$arr['field']['not_defined'] = array ( 'type' => 'toggle' , 'label' => 'Nicht zugewiesen' , 'value' => $map_not_defined , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" );
// }

$arr['field']['autofit'] = array ( 'type' => 'toggle' , 'label' => 'automatisch zoomen' , 'value' => $map_autofit , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" );

$arr['field']['map_zip'] = array ( 'type' => 'dropdown' , 'array' => $array_city2 , 'value' => $map_zip , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" , 'clear' => false ); // 'label' => 'Stadt' ,

$array_places['all'] = 'Alle Plätze';
if ($map_zip != 'all') {
	$query = $GLOBALS['mysqli']->query ( "SELECT name,place_id,
		(SELECT COUNT(*) from tree WHERE district2 = place_id AND tree.trash = '0') count
		FROM tree_places WHERE zip = '$map_zip' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	while ( $fetch_place = mysqli_fetch_array ( $query ) ) {
		$key = $fetch_place['place_id'];
		$array_places[$key] = $fetch_place['name'] . " (" . $fetch_place['count'] . ")";
	}
	
	$arr['field']['map_places'] = array ( 'type' => 'dropdown' , 'array' => $array_places , 'value' => $map_places , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" , 'clear' => false ); // , 'label' => 'Park'
}

if (is_array ( $array_group )) {
	foreach ( $array_group as $key => $values ) {
		$count = $values['count'];
		$title = $values['group'];
		$color = $values['color'];
		$background = $values['background'];
		if ($title) {
			if (is_array ( $_SESSION["map_filter_fruit"] )) {
				if ($_SESSION["map_filter_fruit"][$key])
					$checked = '1';
				else
					$checked = '';
			}
			$arr['field']['fruit_' . $key] = array ( 'type' => 'checkbox' , 'label' => "<span class='label_fruit'><span  style='background:$background'></span></span>$title ($count)" , 'value' => $checked , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" );
		}
	}
}
$arr['field']['hr'] = array ( 'type' => 'content' , 'text' => '<hr>' );
$arr['field']['bicyclinglayer'] = array ( 'type' => 'toggle' , 'label' => 'Fahrradnetz anzeigen' , 'value' => $map_bicyclinglayer , 'onchange' => "$('#fruit_map_filter.ui.form').submit();" );

$output_form = call_form ( $arr );
echo $output_form['html'];
echo $output_form['js'];