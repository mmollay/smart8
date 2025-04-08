<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include ('../../../smart_form/include_form.php');

$query = $GLOBALS ['mysqli']->query ( "SELECT * from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' " );
$array = mysqli_fetch_array ( $query );
$title = $array ['name'];

$arr ['form'] = array ('action' => "$path21/ajax/form_comment2.php" );
$arr ['field'] [] = array ('type' => 'header','text' => $title,'size' => '4','class' => '' );
$arr ['field'] ['difficulty'] = array ('label' => 'Wie ist dir heute gegangen?','type' => 'select','array' => $array_success2,'focus' => true,'validate' => true );
$arr ['field'] ['comment'] = array ('type' => 'textarea','placeholder' => 'Dein Kommentar' );
$arr ['hidden'] ['challenge_id'] = $_POST ['update_id'];
$arr ['buttons'] = array ('align' => 'center' );
$arr ['button'] ['submit'] = array ('value' => 'Kommentieren','color' => 'blue' );
$arr ['button'] ['close'] = array ('value' => 'Schließen','js' => "$('#modal_challenge').modal('hide');" );

$output = call_form ( $arr );
echo $output ['html'];
echo $output ['js'];

?>