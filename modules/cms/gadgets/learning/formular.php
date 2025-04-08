<?php
include_once (__DIR__ . "/../../gadgets/config.php");
include_once ("../../smart_form/include_form.php");
include_once (__DIR__ . '/../function.inc.php'); // für call_layer_parameter

call_layer_parameter ( $_POST['layer_id'] );
$user_id = $_SESSION['user_id'];
$_SESSION['learning_group_id'] = $group_id = $learning_group_id;
$_SESSION['learning_theme_id'] = $theme_id = $learning_theme_id;
$_SESSION['block_id'] = $block_id = 1;
$_SESSION['mode_id'] = $mode_id = 'begin';

$log_on = true;

/**
 * ***********************************************************
 * Check Quizz ist abgeschlossen
 * ***********************************************************
 */

if (! $correctness_percent)
	$correctness_percent = '100';

if (! $success_text)
	$success_text = 'Geschafft...';

if (! $error_text)
	$error_text = 'Leider nicht geschafft...';

//$log_on = true;

// Anzahl der Beantworteten Fragen
$query = $GLOBALS['mysqli']->query ( "SELECT
		(SELECT COUNT(*) FROM ssi_learning.learn_log WHERE correctness='2') count_log_correctness,
		COUNT(*) count_log_all
			FROM ssi_learning.learn_log WHERE group_id='$group_id' AND mode_id ='$mode_id' AND block_nr = '$block_id' AND user_id = '$user_id' " ) or dir ( mysqli_error ( $GLOBALS['mysqli'] ) );
$sql_array = mysqli_fetch_array ( $query );
$count_log_all = $sql_array['count_log_all'];
$count_log_correctness = $sql_array['count_log_correctness'];

// Anzahl der Fragen ermitteln
$query = $GLOBALS['mysqli']->query ( "SELECT COUNT(question_id) count_all FROM ssi_learning.learn_question WHERE group_id='$group_id' AND block_nr = '$block_id' " );
$sql_array2 = mysqli_fetch_array ( $query );
$count_all = $sql_array2['count_all'];
// Wenn alle Fragen beantwortet sind dann Prüfe den Prozentanteil
if ($count_all == $count_log_all and $count_all) {
	$correctness_percent_got = round ( ($count_log_correctness / $count_all) * 100 );
	if ($log_on) {
		$output = "Zu erreichende Prozent für weiter: " . $correctness_percent . "%<br>";
		$output .= "Ereichte Prozent: " . $correctness_percent_got . "%<br>";
		$output .= "Count_All: " . $count_all . "<br>";
		$output .= "Log_Count_All: " . $count_log_all . "<br>";
		$output .= "Log_Count_Correct: " . $count_log_correctness . "<br>";
		$output .= "UserID: " . $user_id . "<br>";
		$output .= "<hr>";
	}
	// Prozentanteil der korrekten Fragen ermitteln
	
	if ($correctness_percent < $correctness_percent_got)
		$output .= $success_text;
	else
		$output .= $error_text;
	echo $output;
	exit ();
}

/**
 * ***********************************************************
 * Ruft Formualar auf wenn Quizz noch läuft oder neu aufgerufen wird
 * ***********************************************************
 */
$array['form'] = array ( 'id' => 'form_begin' , 'width' => '1000' , 'size' => 'huge' , 'keyboardShortcuts' => 'true' , 'action' => 'gadgets/learning/answer_check.php' );
$array['ajax'] = array (  'onLoad' => "$('#progress').progress({ label: 'ratio', text: { ratio: '{value} von {total}' } });" , 
		//  'success' => "$('#form_field').replaceWith(data)" ,
		//  'success' => "data",
		 'dataType' => "script" );

$query = $GLOBALS['mysqli']->query ( "SELECT * from ssi_learning.learn_group WHERE group_id = '$group_id' " ) or dir ( mysqli_error ( $GLOBALS['mysqli'] ) );
$sql_array = mysqli_fetch_array ( $query );
$group_name = $sql_array['title'];

// Auslesen der nächsten Frage
$query = $GLOBALS['mysqli']->query ( "
			SELECT a.question_id,title,
				(SELECT COUNT(question_id) FROM ssi_learning.learn_log WHERE group_id='$group_id' AND mode_id ='$mode_id' AND block_nr = '$block_id') count,
				(SELECT COUNT(question_id) FROM ssi_learning.learn_question WHERE group_id='$group_id' AND block_nr = '$block_id') count2
				FROM ssi_learning.learn_question a
				WHERE (SELECT COUNT(*) FROM ssi_learning.learn_log WHERE question_id = a.question_id AND group_id='$group_id' AND mode_id='$mode_id' AND block_nr = '$block_id' )=0
				AND a.theme_id = '{$_SESSION['learning_theme_id']}'
				AND a.group_id = '$group_id'
				AND a.block_nr = '$block_id'
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );

$sql_array = mysqli_fetch_array ( $query );
$question = $sql_array['title'];
$question_id = $sql_array['question_id'];
$count = $sql_array['count'];

// Anzahl der bereits antworteten Fragen
$progress_value = $sql_array['count'];
// Anzahl aller Fragen
$progress_total = $sql_array['count2'];
// Anzahl der noch offenen Fragen
$progress_diverence = $progress_total - $progress_value;

$progress_bar = "<div class='ui green progress' data-value='$progress_value' data-total='$progress_total' id='progress'><div class='bar'><div class='progress'></div></div></div>";

// $array['field'][] = array ( 'type' => 'text' , 'value' => '<div id=check_info></div>' );
$array['field'][] = array ( 'type' => 'header' ,  'text' => "$group_name" , 'size' => 3 );
$array['field'][] = array ( 'type' => 'header' ,  'text' => "$question" , 'size' => '4' , 'class' => '' );

$query = $GLOBALS['mysqli']->query ( "SELECT * from ssi_learning.learn_choice WHERE question_id = '$question_id' " );
while ( $sql_array2 = mysqli_fetch_array ( $query ) ) {
	$title = $sql_array2['title'];
	$choice_id = $sql_array2['choice_id'];
	$choice_letter = chr ( ord ( 'A' ) + $ii );
	
	$ii ++;
	$array['field'][] = array ( 'id' => "check$choice_id" , 'class' => $class_disabled , 'type' => 'checkbox' , 'label' => "<div class='$class'>$choice_letter) $title</div>" , 'value' => $_POST['check' . $choice_id] );
}

$array['field'][] = array ( 'type' => 'content' , 'text' => "$progress_bar" );
$array['button']['submit'] = array ( 'value' => 'Beantworten' , 'color' => 'blue' , 'id' => 'learning_button_text' );

$array['hidden']['check_question_id'] = $question_id;
$array['hidden']['layer_id'] = $_POST['layer_id'];
$output = call_form ( $array );
echo $output['html'] . $output['js'];