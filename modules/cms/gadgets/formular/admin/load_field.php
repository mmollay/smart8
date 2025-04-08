<?php
include_once ('../../../../login/config_main.inc.php');
include_once ("../../../smart_form/include_form.php");
include_once ("../function.php");

$id = $_POST['id'];

$query = $GLOBALS['mysqli']->query ( "SELECT * from {$_SESSION['db_smartkit']}.smart_formular WHERE field_id = $id" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array2 = mysqli_fetch_array ( $query );

include (__DIR__.'/../include_field.php');

$output_form = call_form ( $arr );
echo $output_form['html'];
echo $output_form['js'];