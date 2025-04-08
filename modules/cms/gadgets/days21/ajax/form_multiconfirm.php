<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include ('../../../smart_form/include_form.php');

$query = $GLOBALS['mysqli']->query("SELECT * from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' ");
$array = mysqli_fetch_array($query);
$title = $array['name'];

$update_id = $_POST['update_id'];

// Muss noch aufgebaut werden
// Ziel: Ein Modal - Dialog mit Buttons über die Tage wo nicht abgehandelt wurde
// - nach jeder Bestätigung soll ein Kommentar möglich sein - vielleicht nur mit POPUP

$query1 = $GLOBALS['mysqli']->query ( "SELECT Count(*) from $db_smart.21_sessions WHERE group_id = '{$_POST['update_id']}' AND action = 'success' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query1 );
$success_count = $array[0];

$query1 = $GLOBALS['mysqli']->query ( "SELECT Count(*) from $db_smart.21_sessions WHERE group_id = '{$_POST['update_id']}' AND action = 'fail' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query1 );
$fail_count = $array[0];

// Aufruf Anzahl der Felder bis zu fail
$query1 = $GLOBALS['mysqli']->query ( "SELECT DATEDIFF(failed_date,start_date) count from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query1 );
$count = $array['count'];

if (! $count) {
	$query1 = $GLOBALS['mysqli']->query ( "SELECT DATEDIFF(NOW(),start_date) count from $db_smart.21_groups WHERE challenge_id = '{$_POST['update_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query1 );
	$count = $array['count'] + 1;
}

$arr['form'] = array ( 'action' => "$path21/ajax/form_multiconfirm2.php" , 'size' => 'small'  );
$arr['ajax'] = array (  'success' => "after_form( data ,'{$update_id}'); " ,  'dataType' => "html" );

//$arr['field'][] = array ( 'id' => 'title' , 'type' => 'text' , value=> "<b>".$title."</b>" );
$arr['field'][] = array (  'type' => 'header' , 'text' => $title , 'size' => '3' , 'class' => 'dividing' );

$array_action = array ( 'success' => 'Geschafft' , 'fail' => 'Nicht geschafft' );
//$arr['field'][] = array ('type' => 'div' , 'class' => 'fields' ); // 'label'=>'test'
$query = $GLOBALS['mysqli']->query ( "SELECT nr,action,action_date from $db_smart.21_sessions WHERE group_id = '$update_id' AND action_date < NOW() order by nr " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $array = mysqli_fetch_array ( $query ) ) {
	$ii = $array['nr'];
	$action_date = $array['action_date'];
	if ($array['action'] == 'success') {
		$arr['field']["text"] = array ('type' => 'content' , 'text' => "<b>Tag {$array['nr']}</b> Geschafft ({$array['action_date']})" );
	} else if ($array['action'] == 'fail') {
		$arr['field']["text$ii"] = array ('type' => 'content' , 'text' => "<b>Tag {$array['nr']}</b> Nicht geschafft" );
		break;
	} elseif (! $fail_count) {	
	    $arr ['field'] [] = array ('type' => 'div','class' => 'ui fields equal width message' );
	    $arr['field']["action_day$ii"] = array ( 'type' => 'dropdown' , 'array' => $array_action ,  'focus' => $focus ,  'validate' => true , 'label' => "{$array['nr']}.Tag ($action_date)");
	    $arr['field']["difficulty$ii"] = array ('label' => 'Wie ist dir heute gegangen?','type' => 'select','array' => $array_success2,'focus' => true,'validate' => true );
	    $arr['field']["comment$ii"] = array ( 'type' => 'textarea', 'rows'=>2, 'placeholder'=>'Kommentar' );
	    $arr['field'][] = array ('type' => 'div_close');
	    
	}
}

$arr['hidden']['challenge_id'] = $update_id;
$arr['button']['submit'] = array ( 'value' => 'Bestätigen' , 'color' => 'green' );
$arr['button']['cancel'] = array ( 'value' => 'Abbrechen' , 'color' => 'red' );

$output = call_form ( $arr );
echo $output['html'];
echo "<script type=\"text/javascript\" src=\"$path21/js/form_challenge.js\"></script>";
echo $output['js'];
?>	