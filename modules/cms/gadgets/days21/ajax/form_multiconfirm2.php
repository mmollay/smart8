<?php
/*
 * Einpflegen der Daten in die Datenbank
 * mm@ssi.at am 06.01.2023
 */
include ('../../config.php');
include_once ('../mysql_days21.inc.php');
// require ("../function.php");

foreach ($_POST as $key => $value) {
    $GLOBALS[$key] = $GLOBALS['mysqli']->real_escape_string($value);
}

/*
 * UPDATE CHALLENGE
 */
if ($challenge_id) {

    // prueft die Eingeange von den Selects und updatet in der Datenbank
    for ($ii = 1; $ii <= 21; $ii ++) {

        $action_date = date('Y-m-d', strtotime($start_date . " + $ii days"));
        $action_day = $GLOBALS['action_day' . $ii];
        $comment = $GLOBALS['comment' . $ii];
        $difficulty = $GLOBALS['difficulty' . $ii];

        // Speichern wenn vorhanden
        if ($action_day) {
            $count[$action_day] ++;
            $GLOBALS['mysqli']->query("
            UPDATE $db_smart.21_sessions SET 
                action = '$action_day', 
                comment = '$comment',
                difficulty = '$difficulty'
                    WHERE group_id = '$challenge_id' AND nr = '$ii' ") or die(mysqli_error($GLOBALS['mysqli']));
        }
    }

    // Wenn ein oder mehrere Tage fehlgeschlagen sind
    if ($count['fail']) {
        $GLOBALS['mysqli']->query("UPDATE $db_smart.21_groups SET failed_date = NOW(), status = 'fail' WHERE challenge_id = '$challenge_id' ") or die(mysqli_error($GLOBALS['mysqli']));
        $parameter = 'failed';
        
    } elseif ($count['success']) {
        // Prüft ob alle abgeschlossen sind, wenn ja, dann wird der Parameter auf success gestellt
        $query = $GLOBALS['mysqli']->query("SELECT action FROM $db_smart.21_sessions WHERE group_id = '$challenge_id' AND action = 'success' ");
        $count = mysqli_num_rows($query);
        $parameter = 'multi_success';
        
        // Am 21sten Tag wird die Datenbank auf erfolgreich gesetzt
        if ($count == '21') {
            $GLOBALS['mysqli']->query("UPDATE $db_smart.21_groups SET success_date = NOW(), status = 'success' WHERE challenge_id = '$challenge_id' ") or die(mysqli_error($GLOBALS['mysqli']));
        }
    }
}


if ($parameter)
    echo $parameter;
else
    echo "ok";
?>