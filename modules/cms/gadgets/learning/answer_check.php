<?php
include_once (__DIR__ . "/../../gadgets/config.php");

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['learning_group_id'];
$theme_id = $_SESSION['learning_theme_id'];
$block_id = $_SESSION['block_id'];
$mode_id = $_SESSION['mode_id'];
$layer_id = $_POST['layer_id'];
$check_question_id = $_POST['check_question_id'];

// CHECK Antwort auf die Frage
$query = $GLOBALS['mysqli']->query ( "SELECT choice_id, correctness from ssi_learning.learn_choice WHERE question_id = '$check_question_id' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
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
} else if ($wrong_answer_count) {
	// Antwort falsch
	$check_info = 'Die Antwort ist leider falsch!';
	$check_variation = 'negative';
	$check_icon = 'frown';
	$fire_login = true;
	$check_correctnes = 0;
	// $button_text = 'Weiter';
} else {
	// Antwort richtig
	$check_info = 'Die Antwort ist richtig!';
	$check_variation = 'positive';
	$check_icon = 'smile';
	$fire_login = true;
	$check_correctnes = 2;
	// $button_text = 'Weiter';
}

if ($fire_login) {
	// Ergebnis eintragen in db
	$GLOBALS['mysqli']->query ( "INSERT INTO ssi_learning.learn_log SET
			user_id = '$user_id',
			question_id = '$check_question_id',
			group_id = '$group_id',
			theme_id = '$theme_id',
			mode_id = '$mode_id',
			block_nr = '$block_id',
			correctness = '$check_correctnes'
			" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}

//Anzeigen der Meldung wenn richtig oder falsch ist
if ($show_message_right_or_wrong) {
	// Ausgabe der Info was zu tun ist
	echo "$('.check_info').modal('show');";
	echo "$('.check_info>.content').html(\"<div class='ui $check_variation message icon'><i class='icon $check_icon'></i><div class='content'>$check_info</div></div>\");";
}

if ($button_text)
	echo "$('#learning_button_text').html(\"$button_text\");";

echo "
$.ajax( {
	url      : 'gadgets/learning/formular.php',
	global   : false,
	async    : false,
	type     : 'POST',
	dataType : 'html',
    data     : { layer_id : $layer_id },
	success  : function(data) { $('#question_form').html(data) }
});";

//include ('formular.php');
//echo "$('#question_form').html(\"$output_formular\");";
