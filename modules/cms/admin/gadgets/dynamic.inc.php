<?
if ($_SESSION['smart_page_id']) {
	$query = $GLOBALS['mysqli']->query ( "
	SELECT t1.dynamic_name dynamic_name, layer_id FROM 
		smart_layer t1 
		LEFT JOIN smart_id_site2id_page t2 ON t1.site_id = t2.site_id 
					WHERE dynamic_modus = 1 AND t2.page_id = '{$_SESSION['smart_page_id']}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$layer_id = $array['layer_id'];
		$dynamic_name = $array['dynamic_name'];
		$array_select_dynamic[$layer_id] = $dynamic_name;
	}
}
$arr['field']['select_dynamic'] = array ( 'tab' => 'first' , "label" => "Dynamische Elemente" , "focus" => true , 'placeholder' => '--bitte wählen--' , "type" => "select" , 'array' => $array_select_dynamic , 'value' => $select_dynamic );