<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include ('../../../smart_form/include_form.php');

$query = $GLOBALS['mysqli']->query("SELECT * from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' ");
$array = mysqli_fetch_array($query);
$title = $array['name'];

$arr['form'] = array ( 'action' => "$path21/ajax/form_comment_cancel2.php" );

$arr['field'][] = array (  'type' => 'header' , 'text' => $title , 'size' => '3' , 'class' => 'dividing' );
//$arr['field'][] = array ( 'id' => 'comment' , 'type' => 'textarea' , 'placeholder' => 'Dein Kommentar' );
$arr['field'][] = array ( 'label'=>'Warum hast du die Challenge beendet?', 'id'  => 'cancel_reasion', 'type'=>'select', 'array'=>$array_chancel);
//$arr['field'][] = array ( 'id' => 'motivation_text' , 'type' => 'text' , 'value' => 'Bist unten kannst du dich besser abdrücken :)' );
$arr['field'][] = array ( 'label' => 'Was kann ich besser machen?', 'id' => 'comment_better_way' , 'type' => 'textarea' , 'placeholder' => '' );

$arr['hidden']['challenge_id'] = $_POST['update_id'];
$arr['buttons'] = array ( 'align' => 'center' );
$arr['button']['submit'] = array ( 'value' => 'Kommentieren' , 'color' => 'blue' );
$arr['button']['close'] = array ( 'value' => 'Schließen' );

$output = call_form ( $arr );
echo $output['html'];
echo $output['js'];
?>