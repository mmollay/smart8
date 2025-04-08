<?php
/*
 * Einpflegen der Daten in die Datenbank
 * mm@ssi.at am 06.01.2011
 */
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
// require ("../function.php");

foreach ( $_POST as $key => $value ) {
	$GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string ( $value );

}

if (!$userbar_id) { echo "error"; return;  }


/*
 * UPDATE CHALLENGE
 */
if ($challenge_id) {

    $GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET
	name  = '$name',
	description = '$description',
	target = '$target',
	comment_better_way = '$comment_better_way',
	view_modus = '$view_modus'
	WHERE 	challenge_id  = '$challenge_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	
	// prueft die Eingeange von den Selects und updatet in der Datenbank
	for($ii = 1; $ii <= 21; $ii ++) {
		$action_date = date ( 'Y-m-d', strtotime ( $start_date . " + $ii days" ) );
		$action_day = $GLOBALS['action_day' . $ii] ?? '';
		
		// Speichern wenn vorhanden
		if ($action_day) {
			$count[$action_day] ++;
			$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_sessions SET action = '$action_day' WHERE group_id = '$challenge_id' AND nr = '$ii' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		}
	}
	
	// Wenn ein oder mehrere Tage fehlgeschlagen sind
	if ($count['fail']) {
		$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET failed_date =NOW(), status = 'fail' WHERE challenge_id = $challenge_id" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		$parameter = 'failed';
	} elseif ($count['success']) {
		$parameter = 'success';
		// Prüft ob alle abgeschlossen sind, wenn ja, dann wird der Parameter auf success gestellt
		$query = $GLOBALS['mysqli']->query ( "SELECT action FROM $db_smart.21_sessions WHERE group_id = '$challenge_id' AND action='success'" );
		$count = mysqli_num_rows ( $query );
		// Am 21sten Tag wird die Datenbank auf erfolgreich gesetzt
		if ($count == '21') {
			$GLOBALS['mysqli']->query ( "UPDATE $db_smart.21_groups SET success_date =NOW(), status = 'success' WHERE challenge_id = $challenge_id" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
		}
	}
	
	/*
	 * NEW CHALLENGE
	 */
} else {
	
	$parent_id = $parent_id ?? 0;
	
	$real_hour = date ( 'H' );
	// Ab dieser Stunde soll der nächste Tag gewählt werden
	$last_hour_time = '22';
	
	// Falls ein niedrigeres Datum verwendet wird
	if ($start_date <= date ( 'Y-m-d' ))
		$start_date = date ( 'Y-m-d' );
	
	if ($real_hour >= $last_hour_time and $start_date == date ( 'Y-m-d' )) {
		$start_date = date ( 'Y-m-d', strtotime ( $start_date . ' + 1 days' ) );
	}
	
	$stop_date = date ( 'Y-m-d', strtotime ( $start_date . ' + 21 days' ) );
	
	$query = "
	INSERT INTO $db_smart.21_groups SET
	user_id  = $userbar_id,
	name  = '$name',
	description = '$description',
	target = '$target',
	result = '$result',
	agree = '$agree',
	view_modus = '$view_modus',
	start_date  = '$start_date',
	stop_date   = '$stop_date',
	parent_id   = $parent_id,
	failed_date  = '0000-00-00',
    success_date = '0000-00-00',
	progressbar_date = '0000-00-00',
	progressbar = '',
	comment_better_way = '',
	cancel_reasion = '',
	status = '',
    difficulty= 0,
	`option` = '$option'
	";
	

	$GLOBALS['mysqli']->query ($query) or die ( mysqli_error ($GLOBALS['mysqli']) );
	
	$last_id = mysqli_insert_id($GLOBALS['mysqli']);
    
	
	// Erzeugt 21 Tage im Vorraus
	for($ii = 1; $ii <= 21; $ii ++) {
		$date_count = $ii - 1;
		$action_date = date ( 'Y-m-d', strtotime ( $start_date . " + $date_count days" ) );
		
		$GLOBALS['mysqli']->query ( "INSERT INTO $db_smart.21_sessions SET
		group_id =	'$last_id',
		action_date = '$action_date',
		nr = '$ii',
        action = 'unknown',
        comment = '',
        difficulty = 0
		" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	}
	
}

if ($parameter)
	echo $parameter;
else
	echo "ok";
?>