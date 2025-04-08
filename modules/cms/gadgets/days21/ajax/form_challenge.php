<?php
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
include ('../../../smart_form/include_form.php');

if (!$userbar_id) {
	echo "<iframe src=\"gadgets/login/\"  style=\"border: 0; width:100%; height:500px;\" >"; return;
}

$tab_select = 'first';
$last_hour_time = '18'; // Ab dieser Stunde soll der nächste Tag gewählt werden
$real_hour = date ( 'H' );
$option = $_POST['option'] ?? '';
$update_id = $_POST['update_id'];



if ($update_id) {
	$read_only = true; // Datum kann bei Update nicht verändert werden
	$arr['sql'] = array ( 'query' => "
	SELECT challenge_id,target,result,challenge_id, name, description,view_modus, difficulty,
	DATE_FORMAT(start_date,'%Y-%m-%d') start_date,
	DATE_FORMAT(stop_date,'%Y-%m-%d') stop_date
	from $db_smart.21_groups WHERE challenge_id = '$update_id' " );
}

if ($real_hour >= $last_hour_time) {
	$start_date = date ( 'Y-m-d', strtotime ( $start_date . ' + 1 days' ) );
} else {
	$start_date = date ( 'Y-m-d' );
}


if ($update_id) {
	
	$query1 = $GLOBALS['mysqli']->query ( "SELECT Count(*) from $db_smart.21_sessions WHERE group_id = '$update_id' AND action = 'success' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query1 );
	$success_count = $array[0];
	
	$query1 = $GLOBALS['mysqli']->query ( "SELECT Count(*) from $db_smart.21_sessions WHERE group_id = '$update_id' AND action = 'fail' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query1 );
	$fail_count = $array[0];
	
	// Aufruf Anzahl der Felder bis zu fail
	$query1 = $GLOBALS['mysqli']->query ( "SELECT DATEDIFF(failed_date,start_date) count from $db_smart.21_groups WHERE challenge_id = '$update_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query1 );
	$count = $array['count'];
	
	if (! $count) {
		$query1 = $GLOBALS['mysqli']->query ( "SELECT DATEDIFF(NOW(),start_date) count from $db_smart.21_groups WHERE challenge_id = '$update_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		$array = mysqli_fetch_array ( $query1 );
		$count = $array['count'] + 1;
	}
	
	// Es werden nie mehr als 21 Tage angezeigt
	if ($count >= '21')
		$count = 21;
	

	$arr['tab'] = array ( 'tabs' => [ "first" => "Allgmein" , "third" => "Endbericht" ] , 'active' =>$tab_select );
}
else $set_puplic = 1;

// Weitermachen, Wiederholen, Mitmachen (wird jeweils die parent_ID der Challenge übergeben und gespeichert
if ($option) {
	$arr['hidden']['parent_id'] = $update_id;
	$arr['hidden']['option'] = $option;
	$read_only_option = true;
	
	if ($option == 'participate') {
		$try_count = " <label class='label green mini ui'>Mitmachen</label>";
	} elseif ($option == 'continue') {
		$try_count = " <label class='label green mini ui'>Weitermachen</label>";
	} elseif ($option == 'clone') {
		$try_count = " <label class='label green mini ui'>Versuch:" . call_count_trys ( $update_id ) . "</label>"; // Auslesen Anzahl der Versuche
	}
	$update_id = '';
	// Wenn Update erfolgt wird update_id übergeben
} elseif ($update_id) {

	$arr['hidden']['challenge_id'] = $update_id;
}

$arr['form'] = array ( 'action' => "$path21/ajax/form_challenge2.php" , 'size' => 'small'  );
$arr['ajax'] = array (  'success' => "after_form( data ,'{$update_id}'); " ,  'dataType' => "html" );

if ($read_only)
    $arr['field']['start_date'] = array ( 'tab' => 'first' , 'class' => 'inline,' , 'label' => 'Start Datum: ' , 'disabled' => true, 'read_only' => $read_only , 'type' => 'input' , 'value' => $start_date );
else
    $arr['field']['start_date'] = array ( 'tab' => 'first' , 'class' => 'inline' , 'label' => 'Start Datum: ' , 'read_only' => $read_only , 'type' => 'date' , 'setting' =>"'type':'date', minDate: new Date()" , 'value' => $start_date );


if ($option)
	$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'name' , 'label' => "Projektbezeichnung: $try_count" , 'type' => 'input' , 'read_only' => true );
else
	
	$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'name' , 'label' => "Projektbezeichnung: $try_count" , 'read_only' => $read_only_option , 'type' => 'input' , 'placeholder' => 'Projektbezeichung' ,  'validate' => 'Projektnamen anführen',  'focus' => true );

$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'description' , 'type' => 'textarea' , 'label' => 'Beschreibe deine Challenge' , 'style' => 'height:60px' ,  'validate' => 'Bitte beschreibe deine Challenge' );
$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'target' , 'type' => 'textarea' , 'label' => 'Was ist ein Ziel?' , 'style' => 'height:40px' ,  'validate' => 'Was willst du mit der Challenge erreichen' );
$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'view_modus' , 'type' => 'toggle' , 'label' => 'Challenge öffentlich anzeigen' , 'value' => $set_puplic );

if (! $update_id)
	$arr['field'][] = array ( 'tab' => 'first' , 'id' => 'agree' , 'type' => 'toggle' , 'label' => 'Ich stimme zu, die Challenge gewissenhaft durchzuführen!' ,  'validate' => 'Bitte bestätigen' );

if (! $option) {
	if ($success_count == '21') {
		$arr['field'][] = array ( 'tab' => 'third' , 'label'=>'Wie ist dir im Gesamten gegangen', id  => 'difficulty', type=>'select', 'array'=>$array_success);
		$arr['field'][] = array ( 'tab' => 'third' , 'label'=>'Dein Erfolgsbericht', 'id' => 'result' , 'type' => 'textarea' , 'placeholder' => 'Schreibe etwas über deinen Erfolg' ,  'validate' => true );
	} elseif ($fail_count) {
		$arr['field'][] = array ( 'tab' => 'third' , 'id' => 'result' , 'type' => 'textarea' , 'placeholder' => 'Was ist schief gelaufen' ,  'validate' => true );
		$arr['field'][] = array ( 'tab' => 'third' , 'id' => 'comment_better_way' , 'type' => 'textarea' , 'placeholder' => 'Was kannst ich besser machen' ,  'validate' => true );
	}
}

$arr['button']['submit'] = array ( 'value' => 'Speichern' , 'color' => 'blue' );
$arr ['button'] ['close'] = array ('value' => 'Schließen','js' => "$('#modal_challenge').modal('hide');" ); 

$output = call_form ( $arr );
echo $output['html'];
echo "<script type=\"text/javascript\" src=\"$path21/js/form_challenge.js\"></script>";
echo $output['js'];

// Auslesen Anzahl der bisherigen Versuche
function call_count_trys($group_id) {
	global $db_smart;
	
	$userbar_id = $_SESSION['userbar_id'];
	$sql = $GLOBALS['mysqli']->query ( "SELECT * from $db_smart.21_groups WHERE user_id = '$userbar_id'  AND parent_id = '$group_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$count = mysqli_num_rows ( $sql );
	if (! $count)
		$count = 1;
	return ++ $count;
}
?>