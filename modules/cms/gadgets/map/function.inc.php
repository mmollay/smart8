<?

/*
 * Toggle für weitere Parameter für das Map-Filter
 */
function call_toggle($id,$title) {
	if ($_SESSION["map_filter"][$id])
		$checked[$id] = 'checked';
	else
		$checked[$id] = '';

	return "
	<div class='field'>
	<div style='left:4px; position:relative;' class='ui toggle checkbox'><input $checked[$id] onclick=onclick=set_filter('$id','checkbox') type=checkbox value=1 name='$id' id='$id'><label for='$id'>$title</label></div>
	</div>
	";
}

/**
 * ******************************************************
 * Dropdownmenu - Für Filter
 *
 * @param unknown $filter_id
 * @param unknown $title
 * @param unknown $array_state
 * wird beim Bazar und Fruitmap eingesetzt
 * ******************************************************
 */
function call_filter_dropdown($filter_id, $title, $array, $default = false, $class = 'huge selection fluid') {
	$filter_value = $_SESSION["map_filter"][$filter_id];
	if (! $filter_value)
		$filter_value = 'all';
	
	if (! $default)
		$default = 'Alle';
	
	$group_a .= "<div class='item' data-value='all' $set_font_size>$default</div>";
	if (is_array ( $array )) {
		foreach ( $array as $key => $value ) {
			$count = call_dropdown_count ( $filter_id, $key );
			if ($count) {
				$count = "<span style='color: grey'>$count</span>";
				$group_a .= "<div class='item' data-value='$key' style='font-size:$font_size'>$value ($count)</div>";
			}
		}
	}
	
	return "
	<div class='ui dropdown $class $filter_id' $set_font_size>
	$button_remove
	<input name='$filter_id' type='hidden' value='$filter_value'>
	<div class='default text'>$title</div>
	<div class='menu'>$group_a</div>
	<i class='dropdown icon'></i>
	</div>
	<script>$(document).ready(function() { $('.$filter_id.ui.dropdown').dropdown({onChange(value, text) { call_filter_map('$filter_id',value); } }); });</script>
	";
}

/**
 * ******************************************************
 * COUNT für alle Filter
 * ******************************************************
 */
function call_dropdown_count($filter_id, $key) {
	$add_mysql = $GLOBALS['add_mysql'];
	
	$table = "
	tree LEFT JOIN (client,tree_template,tree_template_lang,tree_group_lang)
	ON client.client_id = tree.client_faktura_id
	AND tree_template_lang.temp_id = tree_template.temp_id
	AND tree_group_lang.matchcode = tree_template.tree_group
	AND tree.plant_id = tree_template.temp_id
	AND tree_group_lang.lang = 'de'
 ";
	
	// STATE - COUNT
	if ($filter_id == 'map_zip')
		$sql = "SELECT COUNT(*) FROM $table WHERE tree.zip = '$key' AND trash = 0 $add_mysql ";
	elseif ($filter_id == 'map_places')
		$sql = "SELECT COUNT(*) FROM $table WHERE district2 = '$key' AND trash = 0 $add_mysql ";
	else
		return;
	
	$query = $GLOBALS['mysqli']->query ( $sql ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query );
	$count = $array[0];
	
	return $count;
}


//Erzeugt eine array für Dropdown
function call_sql_array($sql) {
	$query = $GLOBALS['mysqli']->query ( $sql ) or die(mysqli_error());
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$array2[$array[0]] = $array[1];
	}
	return $array2;
}