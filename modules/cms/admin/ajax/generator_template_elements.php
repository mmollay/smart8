<?php
// Vorlage für das erzeugen von Elementen einer neuen Seite
$temp_title = "<h1>$site_title</h1>";
$temp_text_long = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';
$temp_text_short6 = $temp_text_short5 = $temp_text_short4 = $temp_text_short3 = $temp_text_short2 = $temp_text_short1 = 
'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam.';

$gaget_array_awesome = array('icon'=>'copy','color'=>'orange','parallax_padding'=>'20');

switch ($template) {
	
	case 'title_text_2col' :
		
		// $generate_array['splitter_title'] = array ( 'gadget' => 'splitter' , 'gadget_array' => 'column_relation=1' );
		// $generate_array['temp_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield', 'splitter_layer_id' => 'splitter_title' );
		
		$generate_array['splitter'] = array ( 'gadget' => 'splitter' , 'gadget_array' => array('column_relation'=>'12') );
		$generate_array['temp_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter' );
		$generate_array['temp_text_long'] = array ( 'splitter_layer_id' => 'splitter' , 'position' => 'left' , 'gadget' => 'textfield' );
		$generate_array['awesome'] = array ( 'position' => 'right' , 'gadget' => 'awesome' , 'splitter_layer_id' => 'splitter' , 'gadget_array' => $gaget_array_awesome );
		$generate_array['temp_text_short1'] = array ( 'position' => 'right' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		break;
	
	case 'title_text_1col' :
		// $generate_array['splitter_title'] = array ( 'gadget' => 'splitter' , 'gadget_array' => 'column_relation=1' );
		
		$generate_array['splitter'] = array ( 'gadget' => 'splitter' ,  'gadget_array' => array('column_relation'=>'1') );
		$generate_array['temp_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter' );
		$generate_array['temp_text_long'] = array ( 'splitter_layer_id' => 'splitter' , 'position' => 'left' , 'gadget' => 'textfield' );
		$generate_array['awesome'] = array ( 'position' => 'right' , 'gadget' => 'awesome' , 'splitter_layer_id' => 'splitter' , 'gadget_array' => $gaget_array_awesome);
		$generate_array['temp_text_short1'] = array ( 'splitter_layer_id' => 'splitter' , 'position' => 'left' , 'gadget' => 'textfield' );
		break;
	
	case 'title_text_3col' :
		
		$generate_array['splitter_title'] = array ( 'gadget' => 'splitter' , 'gadget_array' => array('column_relation'=>'1') );
		$generate_array['temp_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter_title' );
		
		$generate_array['splitter'] = array ( 'gadget' => 'splitter' , 'gadget_array' => array('column_relation'=>'333') );
		$generate_array['temp_text_short1'] = array ( 'position' => 'left' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		$generate_array['awesome1'] = array ( 'position' => 'left' , 'gadget' => 'awesome' , 'splitter_layer_id' => 'splitter' , 'gadget_array' => $gaget_array_awesome );
		$generate_array['temp_text_short2'] = array ( 'position' => 'left' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		
		$generate_array['temp_text_short3'] = array ( 'position' => 'middle' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		$generate_array['awesome2'] = array ( 'position' => 'middle' , 'gadget' => 'awesome' , 'splitter_layer_id' => 'splitter' , 'gadget_array' => $gaget_array_awesome );
		$generate_array['temp_text_short4'] = array ( 'position' => 'middle' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		
		$generate_array['temp_text_short5'] = array ( 'position' => 'right' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		$generate_array['awesome3'] = array ( 'position' => 'right' , 'gadget' => 'awesome' , 'splitter_layer_id' => 'splitter' , 'gadget_array' => $gaget_array_awesome  );
		$generate_array['temp_text_short6'] = array ( 'position' => 'right' , 'splitter_layer_id' => 'splitter' , 'gadget' => 'textfield' );
		break;
	
	default :
		$generate_array['splitter'] = array ( 'gadget' => 'splitter' , 'gadget_array' => array('column_relation'=>'1') );
		$generate_array['temp_title'] = array ( 'position' => 'left' , 'gadget' => 'textfield' , 'splitter_layer_id' => 'splitter' );
		$generate_array['temp_text_long'] = array ( 'splitter_layer_id' => 'splitter' , 'position' => 'left' , 'gadget' => 'textfield' );
		break;
}