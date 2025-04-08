<?php
include ('../../../../login/config_main.inc.php');

$site_id = $_SESSION ['site_id'];

if ($_POST ['save_id']) {

	$id = $GLOBALS ['mysqli']->real_escape_string ( $_POST ['save_id'] );
	// $validate = $GLOBALS['mysqli']->real_escape_string ( $_POST['validate'] );
	// $placeholder = $GLOBALS['mysqli']->real_escape_string ( $_POST['placeholder'] );
	// $help = $GLOBALS['mysqli']->real_escape_string ( $_POST['help'] );
	// $rows = $GLOBALS['mysqli']->real_escape_string ( $_POST['rows'] );
	// $label = $GLOBALS['mysqli']->real_escape_string ( $_POST['label'] );
	// $set_email = $GLOBALS['mysqli']->real_escape_string ( $_POST['set_email'] );
	// $newsletter_field = $GLOBALS['mysqli']->real_escape_string ( $_POST['newsletter_field'] );
	// $default_value = $GLOBALS['mysqli']->real_escape_string ( $_POST['default_value'] );

	foreach ( $_POST as $key => $value ) {
		if ($value) {
			$GLOBALS [$key] = $GLOBALS ['mysqli']->real_escape_string ( $value );
		}
	}

	set_update_site ();

	if ($_POST ['value']) {
		if (strpos ( $_POST ['value'], '=>' )) {
			$gadget_array_n = explode ( "\n", $_POST ['value'] );
			foreach ( $gadget_array_n as $set_array ) {

				if ($set_array) {
					$array3 = preg_split ( "[=>]", $set_array, 2 );

					if ($array3 [1])
						$array_value [$array3 [0]] = $array3 [1];
				}
			}
			
		} else {
			$array_value = explode ( "\n", $_POST ['value'] );
		}
		$value = json_encode ( $array_value, true );
		$value = $GLOBALS ['mysqli']->real_escape_string ( $value );
	}
	
	$new_array = array ('select','segment','segment_color','segment_inverted','segment_disabled','segment_grade','segment_or_message','rows','min','max','class_ticked','class_color','unit' );

	foreach ( $new_array as $key ) {
		$array_value = $GLOBALS ['mysqli']->real_escape_string ( $_POST [$key] );
		if ($array_value) {
			$array_new .= $key . "=" . $array_value . "|";
			$array_new = preg_replace ( "/|/", "", $array_new );
		}
	}

	// Prüfen ob der User die Berechtigung hat auf dieser Seite was zu ändern
	$query = $GLOBALS ['mysqli']->query ( "SELECT site_id FROM smart_layer a LEFT JOIN smart_formular b ON a.layer_id = b.layer_id WHERE field_id = '$id' and site_id = $site_id " );
	$check = mysqli_num_rows ( $query );

	if (!$set_email) $set_email = 0;
	
	if ($check) {
		$GLOBALS ['mysqli']->query ( "UPDATE smart_formular SET 
		value='$value',
		default_value = '$default_value',
		validate = '$validate',
		label = '$label',
		help = '$help',
		set_email = $set_email,
        text = '$text',
		placeholder = '$placeholder',
		setting_array = '$array_new',
		newsletter_field = '$newsletter_field'
		WHERE field_id = '$id' LIMIT 1 " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
		echo "ok";
	}
} else {
	echo "error";
}