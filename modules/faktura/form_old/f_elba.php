<?php
$sql_automator_id = $GLOBALS ['mysqli']->query ( "SELECT automator_id,text FROM data_elba where elba_id = '{$_POST['update_id']}' " ) or die ( mysqli_error ( $GLOBALS ['mysqli'] ) );
$array_elba = mysqli_fetch_array ( $sql_automator_id );
$text = $array_elba ['text'];

//call automator_id
$automator_id = $array_elba ['automator_id'];

if ($_POST ['update_id']) {
	$arr ['sql'] = array ('query' => "SELECT * from automator WHERE automator_id = '$automator_id' " );
}

$arr ['field'] ['text'] = array ('type' => 'content','text' => $text,'class' => 'ui message' );

$arr ['field'] [] = array ('type' => 'div','class' => 'fields' );
$arr ['field'] ['automator_id'] = array ('class' => 'ten wide search','type' => 'dropdown','label' => 'Automator',
		'array_mysql' => '
SELECT a.automator_id,CONCAT(a.description," (",c.title,")") FROM automator a 
	LEFT JOIN automator b ON a.automator_id = b.automator_id
	LEFT JOIN accounts c ON b.account_id = c.account_id
','validate' => true,'placeholder' => 'Automator wählen','focus' => true,'onchange' => "call_word_list(value)" );

$arr ['field'] ['add_automator'] = array ('type' => 'button','onclick' => "call_semantic_form('','modal_form_automator_edit','ajax/form_edit.php','add_automator_form','')",'value' => ' Automator anlegen','label' => ' ', 'icon'=>'robot' );
$arr ['field'] [] = array ('type' => 'div_close' );

$arr ['field'] ['word'] = array ('type' => 'textarea','label' => 'Schlüsselwörter','placeholder' => 'A1 Telekom','validate' => true,'search' => true,'focus' => true );

$success = "
	if ( data  == 'ok' ) {
        $('body').toast({message: 'Automation wurde gespeichert'});
		table_reload();
        $('#modal_form_edit').modal('hide');
	}
";

$arr ['ajax'] = array ('success' => $success,'dataType' => "html" );

$add_js .= "<script type=\"text/javascript\" src=\"js/elba.js\"></script>";
