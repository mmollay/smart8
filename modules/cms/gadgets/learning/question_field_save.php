<?php
include (__DIR__ . "/../../gadgets/config.php");
include (__DIR__ . '/../function.inc.php');

if (! $_SESSION['block_id'])
	$_SESSION['block_id'] = 1;

$block_id = $_SESSION['block_id'];
// es können in weiterer Folge beliebig viele Modies wie "schnuppern","lernen","fokus","prüfung simulieren",usw... erzeugt werden
$_SESSION['mode_id'] = 'begin';

call_layer_parameter ( $_POST['layer_id'] );
$_SESSION['learning_group_id'] = $learning_group_id;
$_SESSION['learning_theme_id'] = $learning_theme_id;

echo "<div id=check_info></div>";
echo "<div id=question_form>";
include (__DIR__.'/formular.php');
echo "</div>";
exit ();

/**
 * **********************************************************************
 * Wenn noch kein Block gewählt wurde, dann stehen die Blöcke zur Auswahl
 * **********************************************************************
 */
if (! $_POST['submit'] and $show_block) {
	$query = $GLOBALS['mysqli']->query ( "SELECT Count(*),block_nr FROM ssi_learning.learn_question WHERE group_id = '$learning_group_id' GROUP by block_nr order by block_nr " );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$id = $array[1];
		$block_count = $array[0];
		if ($block_id == $id) {
			$class = 'green';
			$active_block_count = $block_count;
		} else
			$class = '';
		$block_button .= "<button class='button ui $class' onclick=call_block($id) >Block $id ($block_count)</button>";
	}
	echo $block_button;
	echo "<br><br>";
}

/**
 * **********************************************************************
 * WENN BLOCK GEWÄHLT WURDE
 * **********************************************************************
 */
if ($block_id) {
	if ($_POST['check_question_id']) {
		// CHECK Antwort auf die Frage
		$query = $GLOBALS['mysqli']->query ( "SELECT choice_id, correctness from ssi_learning.learn_choice WHERE question_id = '{$_POST['check_question_id']}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		while ( $sql_array2 = mysqli_fetch_array ( $query ) ) {
			$correctness = $sql_array2['correctness'];
			$choice_id = $sql_array2['choice_id'];
			$check_choice_id = $_POST['check' . $choice_id];
			if ($check_choice_id)
				$count ++;
			if (! $check_choice_id)
				$check_choice_id = '0';
			if ($correctness != $check_choice_id) {
				$wrong_answer_count ++;
			}
		}
		
		// if ($check_choice_id)
		// $answer_count ++;
		// if ($correctness and $check_choice_id) {
		// $_POST['color_' . $choice_id] = 'green';
		// $correct_answer[$choice_id] = $choice_id;
		// } elseif (! $correctness and $check_choice_id)
		// $wrong_answer[$choice_id] = $choice_id;
		
		// keine Antwort gewählt
		if (! $count) {
			$check_info = 'Bitte mindestens eine Antwort wählen';
			$check_variation = 'info';
			$check_icon = 'info';
			// Antwort falsch
		} else if ($wrong_answer_count) {
			$check_info = 'Die Antwort ist leider falsch!';
			$check_variation = 'negative';
			$check_icon = 'frown';
			$next_question = true;
			$check_correctnes = 0;
			// Antwort richtig
		} else {
			$check_info = 'Die Antwort ist richtig!';
			$check_variation = 'positive';
			$check_icon = 'smile';
			$next_question = true;
			$check_correctnes = 2;
			// Teilweise richtig
		}
		
		if ($next_question) {
			
			// Ergebnis eintragen in db
			$GLOBALS['mysqli']->query ( "INSERT INTO ssi_learning.learn_log SET
			user_id = '$user_id',
			question_id = '{$_POST['check_question_id']}',
			group_id = '$learning_group_id',
			theme_id = '$learning_theme_id',
			mode_id = '$mode_id',
			block_nr = '$block_id', 
			correctness = '$check_correctnes'
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		}
	}
	
	include_once ("../../smart_form/include_form.php");
	$array['form'] = array ( 'id' => 'form_begin' , 'width' => '1000' , 'size' => 'huge' , 'class' => 'segment' , keyboardShortcuts => 'true' );
	$array['ajax'] = array (  'onLoad' => "$('#progress').progress({ label: 'ratio', text: { ratio: '{value} von {total}' } });" ,
			success_save => "$('#form_message').html('<div class=\"message green mini ui\">Richtig! <i class=\"icon smile\"></i></div>'); " ,
			 'success' => "$('#form_field').replaceWith(data)" ,
			 'dataType' => "html" );
	
	$query = $GLOBALS['mysqli']->query ( "SELECT * from ssi_learning.learn_group WHERE group_id = '$learning_group_id' " ) or dir ( mysqli_error ( $GLOBALS['mysqli'] ) );
	$sql_array = mysqli_fetch_array ( $query );
	$group_name = $sql_array['title'];
	
	if ($last_question_id) {
		// Aufruf einer bestehenden Frage
		$query = $GLOBALS['mysqli']->query ( "
		SELECT a.question_id,title,
		(SELECT COUNT(question_id) FROM ssi_learning.learn_log WHERE group_id='$learning_group_id' AND mode_id ='$mode_id' AND block_nr = $block_id) count,
		(SELECT COUNT(question_id) FROM ssi_learning.learn_question WHERE group_id='$learning_group_id' AND block_nr = '$block_id') count2
		FROM ssi_learning.learn_question a WHERE question_id = $last_question_id
		" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	} else {
		
		// Auslesen der nächsten Frage
		$query = $GLOBALS['mysqli']->query ( "
			SELECT a.question_id,title,
				(SELECT COUNT(question_id) FROM ssi_learning.learn_log WHERE group_id='$learning_group_id' AND mode_id ='$mode_id' AND block_nr = '$block_id') count, 
				(SELECT COUNT(question_id) FROM ssi_learning.learn_question WHERE group_id='$learning_group_id' AND block_nr = '$block_id') count2
				FROM ssi_learning.learn_question a				
				WHERE (SELECT COUNT(*) FROM ssi_learning.learn_log WHERE question_id = a.question_id AND group_id='$learning_group_id' AND mode_id='$mode_id' AND block_nr = '$block_id' )=0
				AND a.group_id = '$learning_group_id' 
				AND a.block_nr = '$block_id' 
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
	}
	
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
	
	if ($check_info) {
		$array['field'][] = array ( 'type' => 'text' , 'value' => "<div class='ui $check_variation message mini icon'><i class='icon $check_icon'></i><div class='content'>$check_info</div></div>" );
	}
	$array['field'][] = array ( 'type' => 'header' ,  'text' => "$group_name" , 'size' => 3 );
	
	// AUFRUF BEI FRAGE
	if ($question_id) {
		
		$array['field'][] = array ( 'type' => 'header' ,  'text' => "$question" , 'size' => '4' , 'class' => '' );
		if ($next_question)
			$class_disabled = ' disabled';
		else
			$class_disabled = '';
		
		$query = $GLOBALS['mysqli']->query ( "SELECT * from ssi_learning.learn_choice WHERE question_id = $question_id " );
		while ( $sql_array2 = mysqli_fetch_array ( $query ) ) {
			$title = $sql_array2['title'];
			
			$choice_id = $sql_array2['choice_id'];
			$choice_letter = chr ( ord ( 'A' ) + $ii );
			if ($_POST['check_' . $choice_id])
				$class = 'ui header green tiny';
			else
				$class = '';
			
			$ii ++;
			$array['field'][] = array ( 'id' => "check$choice_id" , 'class' => $class_disabled , 'type' => 'checkbox' , 'label' => "<div class='$class'>$choice_letter) $title</div>" , 'value' => $_POST['check' . $choice_id] );
		}
		
		$array['field'][] = array ( 'type' => 'text' , 'value' => "$progress_bar" );
		// $array['buttons'] = array('align'=>'center');
		
		if ($next_question) {
			$button_text = 'Nächste Frage aufrufen';
			$array['hidden']['call_next_question'] = 1;
		} else {
			$button_text = "Frage beantworten";
			$array['hidden']['last_question_id'] = $question_id;
			$array['hidden']['check_question_id'] = $question_id;
		}
		$array['button']['submit'] = array ( 'value' => $button_text , 'color' => 'blue' );
	} else {
		// WENN KEINE FRAGE MEHR VORHANDEN SIND
		$array['field'][] = array ( 'type' => 'header' , 'class' => 'aligned center' ,  'text' => "<div class=\"message info mini ui\">Keine Fragen vorhanden!</div>" );
	}
	
	$array['hidden']['submit'] = 1;
	$output = call_form ( $array );
	echo "<div id ='form_field'>";
	echo $output['html'] . $output['js'];
	echo "</div>";
} else {
	echo "<div class='message large icon ui' style='max-width:800px'> <i class='inbox icon'></i><div class='content'> <div class='header'>Bitte Block wählen</div>Der Lernstoff wurde in Blöcke aufgeteilt um ein übersichtlicheres Lernen zu gewähren!</div></div>";
}
