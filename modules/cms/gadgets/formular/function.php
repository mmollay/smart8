<?php
function call_segment($gadget_array) {
	$gadget_array_n = explode ( "|", $gadget_array );
	if ($gadget_array) {
		foreach ( $gadget_array_n as $array ) {
			$array2 = preg_split ( "[=]", $array, 2 );
			${$array2[0]} = $array2[1];
			// liefert aus der Funktion die Parameter für die Darstellung und Erweiterungen der Felder
			$array_return[$array2[0]] = $array2[1];
		}
	}
	
	if ($segment) {
		if (! $segment_or_message)
			$segment_or_message = 'segment';
		if ($segment_inverted)
			$segment_inverted = 'inverted';
		if ($segment_disabled)
			$segment_disabled = 'disabled';
		
		if ($segment_color == 'transparent')
			$segment_color = '';
		elseif ($segment_color) {
			$segment_color = "$segment_color";
		}
		
		$segment = "$segment_size $segment_or_message $segment_color $segment_inverted $segment_grade $segment_disabled ui ";
		$segment_inverted = '';
		$segment_color = '';
		$sement_disabled = '';
		$segment_grade = '';
	} else {
		$segment = '';
	}
	$array_return['segment'] = $segment;
	return $array_return;
}

// Auslesen der Felder
function show_field($id) {
	$query = $GLOBALS['mysqli']->query ( "SELECT * from {$_SESSION['db_smartkit']}.smart_formular WHERE field_id = '$id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$array2 = mysqli_fetch_array ( $query );
	
	$field_id = $array2['field_id'];
	$type = $array2['type'];
	$value = json_decode ( $array2['value'], true );
	$label = $array2['label'];
	$text = $array2['text'];
	$help = $array2['help'];
	$placeholder = $array2['placeholder'];
	$validate = $array2['validate'];
	$gadget_array = $array2['setting_array'];
	$segment_formular_field = call_segment ( $gadget_array );
	$rows = $segment_formular_field['rows'];
	
	if ($validate == '1')
		$validate = true;
	else
		$validate = false;
	
	if ($_SESSION['admin_modus'])
		$setting = "contenteditable='true'";
	
	if ($type == 'splitter') {
		
		// $query = $GLOBALS['mysqli']->query ( "SELECT field_id,position from {$_SESSION['db_smartkit']}.smart_formular WHERE splitter_field_id = '$field_id' AND field_id != '$field_id' ORDER by sort" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		// while ( $array2 = mysqli_fetch_array ( $query ) ) {
		// $position = $array2['position'];
		// $field_content[$position] = show_field($array2['field_id'], $new = false);
		// }
		// $arr['field'][] = array ( 'type' => 'div' , 'class' => 'two fields' );
		// $arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );
		// $arr['field']['title1'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' ,  'validate' => true );
		// $arr['field']['title2'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' ,  'validate' => true );
		// $arr['field'][] = array ( 'type' => 'div_close' );
		// $arr['field'][] = array ( 'type' => 'div' , 'class' => 'fields' );
		// $arr['field']['title3'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' ,  'validate' => true );
		// $arr['field']['title4'] = array ( 'type' => 'input' , 'label' => 'Beschreibung' ,  'validate' => true );
		// $arr['field'][] = array ( 'type' => 'div_close' );
		// $arr['field'][] = array ( 'type' => 'div_close' );
		
		// $splitter .= "
		// <div id='left_{$layer_id}_{$field_id}' class='eight wide column sortable_formular'>{$field_content['left']}</div>
		// <div id='right_{$layer_id}_{$field_id}' class='eight wide column sortable_formular'>{$field_content['right']}</div>
		// ";
		// $arr = array ( 'class' => "ui grid {$segment_formular_field['segment']}" , 'type' => 'content' ,  'text' => $splitter );
	} elseif ($type == 'text') {
		$arr = array ( 'class' => $segment_formular_field['segment'] , 'setting' => $setting , 'type' => 'content' ,  'text' => $text );
	} elseif ($type == 'select' or $type == 'radio')
		$arr = array ( 'class' => $segment_formular_field['segment'] , 'label_class' => 'formular' , 'label' => $label , 'type' => $type , 'array' => $value ,  'validate' => $validate , 'placeholder' => '--Bitte wählen--' , 'info' => $helps );
	else
		$arr = array ( 'class' => $segment_formular_field['segment'] , 'label_class' => 'formular' , 'label' => $label , 'rows' => $rows , 'type' => $type ,  'validate' => $validate , 'placeholder' => $placeholder , 'info' => $help );
	
	return $arr;
}
