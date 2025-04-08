<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include ('../../../smart_form/include_form.php');

$query = $GLOBALS['mysqli']->query("SELECT * from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' ");
$array = mysqli_fetch_array($query);
$title = $array['name'];

$arr['form'] = array ( 'action' => "$path21/ajax/form_comment_success2.php" );
$arr['field'][] = array (  'type' => 'header' , 'text' => $title , 'size' => '3' , 'class' => 'dividing' );
$arr['field'][] = array ( 'label'=>'Wie ist dir im Gesamten gegangen?', id  => 'difficulty', type=>'select', 'array'=>$array_success,  'focus' => true);
$arr['field'][] = array ( 'id' => 'comment' , 'type' => 'textarea' , 'placeholder' => 'Schreibe etwas über deinen Erfolg' );
$arr['hidden']['challenge_id'] = $_POST['update_id'];
$arr['buttons'] = array ( 'align' => 'center' );
//$arr['button']['submit'] = array ( 'value' => 'Kommentieren' , 'color' => 'blue' );
$arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Schließen' );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];
?>