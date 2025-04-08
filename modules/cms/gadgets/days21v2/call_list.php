<?php
// if ($_POST['ajax'] == true) {
include ('../config.php');
include_once ('mysql_days21.inc.php');
include_once ('function.php');
$date = date('Y-m-d');
$cfg_max_length = 50;
$limit = "LIMIT 100";

$sql = "SELECT
		challenge_id,
		DATE_FORMAT(start_date,'%Y-%m-%d') start_date,
		progressbar,
		firstname,
		secondname,
		IF(LENGTH(name) >=$cfg_max_length, CONCAT(substring(name, 1,$cfg_max_length), CONCAT('...')), name) name,
		t1.user_id user_id,
		status,
		view_modus,
		description, target, result, comment_better_way,
		DATEDIFF(start_date,NOW()) time_differenz_start,
		(SELECT COUNT(*) FROM $db_smart.21_sessions WHERE challenge_id = group_id AND comment !='' ) count_comment,
		(SELECT COUNT(*) FROM $db_smart.21_sessions WHERE group_id = challenge_id AND action='success') counter_success,
		(SELECT COUNT(*) FROM $db_smart.21_sessions WHERE group_id = challenge_id AND (action='unknown' OR !action) AND now() >= action_date) counter_inactive,
		if (stop_date < now(),true,'') expired
		FROM $db_smart.21_groups t2, ssi_company.user2company t1 WHERE t1.user_id  = t2.user_id $add_mysql order by start_date desc, challenge_id desc  $limit ";


$query = $GLOBALS['mysqli']->query($sql) or die(mysqli_error($GLOBALS['mysqli']));

while ($array = mysqli_fetch_array($query)) {
    $challenge_id = $array['challenge_id'];

    $user_id = $array['user_id'];
    $name = $array['name'];
    $firstname = $array['firstname'];
    $secondname = $array['secondname'];
    $status = $array['status'];
    $counter_now = $array['counter'] ?? '';
    $counter_success = $array['counter_success'];
    $counter_inactive = $array['counter_inactive'];
    $counter_total = $counter_success + $counter_inactive;
    $count_active = $array['count_active'] ?? ''; // Aktive Zeit start bis stop COUNT
                                                   // $start_date = $array['start_date'];
    $description = nl2br($array['description']);
    $target = nl2br($array['target']);
    $result = nl2br($array['result']);
    $break = nl2br($array['comment_better_way']);
    $count_comment = $array['count_comment'];
    $start_date = $array['start_date'];
    $time_differenz_start = $array['time_differenz_start'];
    $view_modus = $array['view_modus'];

    date_default_timezone_set('Europe/Berlin');
    $datetime1 = date_create($start_date);
    $datetime2 = date_create();
    $interval = date_diff($datetime1, $datetime2);
    $start_date = $interval->format('%a');
    if ($start_date == 1)
        $start_date = "$start_date Tag";
    else
        $start_date = "$start_date Tagen";

    if ($view_modus == 'private' or ! $view_modus)
        $view_modus = "<i class='icon hide  orange tooltip' title='nicht Öffentlich angezeigt'></i>";
    elseif ($view_modus == 'public' or $view_modus)
        $view_modus = "<i class='icon unhide  green tooltip' title='Öffentlich angezeigt'></i>";

    if ($array['start_date'] > date('Y-m-d'))
        $status = 'prepared';
    else if ($array['expired'] and $counter_inactive and $status == '')
        $status = 'expired';
    else if ($counter_inactive > 4 and $status == '') {
        $status = 'inactive';
        // Auslesen ob Challenge abgelaufen ist
    }

    if (! $status)
        $status = 'active';

    $label_size = 'big';
    switch ($status) {
        // Erfolgreiche Challenge
        case 'success':
            $color = 'green';
            $label_status = "<i class='$label_size trophy yellow icon tootip' title='Challenge geschafft!' ></i>";
            $progress_bar = "<i class='icon checkmark green'></i><span class='ui small green header'>Erfolgreich</span>";
            $counter_total = 21;
            $tooltip_segment = 'Erfolgreiche Challenge';
            break;
        // Abgebrochen
        case 'fail':
            $color = 'red';
            $label_status = "<i class='$label_size remove red icon tooltip' title='Challenge abgebrochen'></i>";
            $progress_bar = "<i class='icon remove red'></i><span class='ui small red header'>Abgebrochen</span>";
            // $progress_bar = call_progress_bar ( $challenge_id );
            $counter_total = $counter_success + 1;
            $tooltip_segment = 'Challenge abgebrochen';
            break;
        // Inaktive also nicht benutzt
        case 'inactive':
            $label_status = "<i class='$label_size wait disabled icon tooltip' title='Challenge ist nicht aktuell'></i>";
            $progress_bar = call_progress_bar($challenge_id);
            $counter_total = $counter_total;
            break;
        // Abgelaufen
        case 'expired':
            $label_status = "<i class='$label_size remove disabled icon tooltip' title='Challenge ist unbestätigt abgelaufen'></i>";
            $progress_bar = "<i class='icon clock grey'></i><span class='ui small grey header'>Abgelaufen</span>";
            $counter_total = $counter_total;
            break;
        // Challenge am Start
        case 'prepared':
            if ($time_differenz_start == 1)
                $count_start_in = "Morgen geht es los";
            else if ($time_differenz_start > 1)
                $count_start_in = "Start in $time_differenz_start Tagen";

            $label_status = "<i class='$label_size remove yellow icon tooltip' title='Challenge ist vor dem Start'></i>";
            $counter_total = $counter_total;
            $progress_bar = "<span class='ui label yellow' style='width:150px'>$count_start_in</span>";

            break;
        // Challenge am laufen
        case 'active':
            $color = 'orange';
            $label_status = "<i class='$label_size video play $color icon tooltip' title='Challenge läuft'></i>";
            $counter_total = $counter_total;
            $progress_bar = call_progress_bar($challenge_id);
    }

    if ($count_comment >= 1) {
        $name = "$name  <i class='ui icon book'></i>$count_comment";
    }

    /**
     * ***********************************************************
     * Admin Modus
     * LOGIN - USER
     * ***********************************************************
     */

    if ($userbar_id) {

        // Abfrage erfolgt auf mysql_day21.inc.php

        if ($userbar_id == $user_id) {
            if ($counter_inactive) {
                if ($counter_inactive == 1) {
                    $title_success = 'Erfolgreichen Tag bestätigen?';
                    $onclick = "call_form_challenge($challenge_id)";
                } else {
                    $title_success = 'Erfolgreiche Tage bestätigen?';
                    $onclick = "call_form_challenge_multi($challenge_id)";
                }
            }

            $add_edit_button = "<div class='item' onclick='call_modal_form($challenge_id)'><i class='icon edit'></i>Bearbeiten</div>";
            $add_edit_button .= "<div class='item' onclick='del_challenge($challenge_id)'><i class='icon trash'></i>Löschen</div>";
            $del_button = true;

            if ($status == 'prepared') {
                $edit_button2 = '';
            } else if ($status == 'fail') {
                $add_edit_button .= "<div class='item' onclick='call_modal_form($challenge_id,\"clone\")'><i class='icon refresh'></i>Nochmal starten</div>";
                $edit_button2 = "<div class='ui icon button tooltip' title='Challenge erneut starten' onclick=\"call_modal_form($challenge_id,'clone')\"><i class='refresh icon'></i></div>";
            } else if ($status == 'success') {
                $add_edit_button .= "<div class='item' onclick='call_modal_form($challenge_id,\"continue\")'><i class='icon star'></i>Fortsetzen</div>";
                $edit_button2 = "<div class='ui icon button tooltip' title='Challenge weiter machen' onclick=\"call_modal_form($challenge_id,'continue')\"><i class='star icon'></i></div>";
            } else if ($counter_inactive) {
                $edit_button2 = "
				<div class='ui icon green button tooltip' title='$title_success' onclick='$onclick'><i class='thumbs up icon'></i></div>
				<div class='ui icon red button tooltip' title='Nicht geschafft?' onclick='cancel_challenge($challenge_id)'><i class='thumbs down icon'></i></div>
				";
            } else {
                $edit_button2 = "<i class='thumbs up green big icon tooltip' title='Challenge heute geschafft'></i>";
            }
        } else {
            $add_edit_button .= "<div class='item' onclick='call_modal_form($challenge_id,\"participate\")'><i class='icon share alternate'></i>Diese Challenge starten</div>";
            $edit_button2 = "<div class='ui icon button tooltip' onclick=\"call_modal_form($challenge_id,'participate')\" title='Diese Challenge starten'><i class='share alternate icon'></i></div>";
        }
        
        $field_edit1 = "<div class='ui tiny buttons'>$edit_button2</div>";
        // $td_field_edit2 = "<td width=100><div class='ui small buttons'>$edit_button1</td></td>";
        $edit_button2 = '';
        $edit_button1 = '';

        // Superuser das bestehende Challenges löschen
        if ($superuser and ! $del_button) {
            $add_edit_button .= "<div class='item' onclick='del_challenge($challenge_id)'><i class='icon trash'></i>Löschen</div>";
        }

        $dropdown_edit = "<span class='dropdown_edit'>";
        $dropdown_edit .= "<div class='ui icon left left pointing dropdown tiny button'>
		<i class='wrench icon'></i>
		<div class='menu'>
		$add_edit_button 
		</div>";
        $dropdown_edit .= "</span></div>";

        $add_edit_button = '';
        $del_button = '';
    }

    /**
     * *************
     * FELDER
     * *************
     */

    // TAGE aus wenn in Vorbereitung
    if ($status == 'prepared') {
        $label_day = '';
    } else {
        $label_day = "<div style='float:left;'><div class='ui right pointing basic mini label tooltip' style='width:30px; text-align:center;' title='$counter_total. Tag' >$counter_total</div></div>";
    }

    // Like bar
    $like_bar = comment_bar($challenge_id, 'challenge', 'icon', $a_show_details) . like_bar($challenge_id, 'challenge');

    if ($user_id == $userbar_id) {
        $line_name = 'own';
    } else
        $line_name = 'all';

    $td_list[$line_name] .= "<tr>";
    if ($dropdown_edit or $field_edit1)
        $td_list[$line_name] .= "<td width=140px;>$dropdown_edit $field_edit1</td>";
    $td_list[$line_name] .= "<td>
				<a class='list_challenge_name tooltip' title='Zeige Details an' onclick=show_details('$challenge_id')>
				$view_modus$name<div style='font-size:10px; color:grey;'>Seit <i>$start_date</i> von <b>$firstname $secondname</b></div>
				</a>
			
				<div class='list_challenge_like'>$like_bar</div>
		
	</td>";
    // $td_list .= $td_field_edit2;
    $td_list[$line_name] .= "<td width=170>$label_day<div style='float:left;'>$progress_bar</div>$edit_button2<div style='clear:both'></div></td>";
    $td_list[$line_name] .= "</tr>";
}

// if (! $td_list) {
// $td_list = "<tr><td colspan=2><div class=empty_insert><h3>Keine Einträge vorhanden</h3></div></td></tr>";
// }

if (! $td_list['own']) {
    $td_list['own'] = "<tr><td colspan=2><div class=empty_insert><h3>Keine Einträge vorhanden</h3></div></td></tr>";
}

if ($userbar_id) {
    $output_list = "<table class='ui celled striped table' id='challenge_list_own'>";
    $output_list .= "<thead><tr><th colspan=3>Meine Challenges</th></tr></thead>";
    $output_list .= $td_list['own'];
    $output_list .= "</table>";
}

if ($_SESSION['show_all'] != 'checked' or ! $userbar_id) {
    if (! $td_list['all']) {
        $td_list['all'] = "<tr><td colspan=2><div class=empty_insert><h3>Keine Einträge vorhanden</h3></div></td></tr>";
    }
    $output_list .= "<table class='ui celled striped table' id='challenge_list_all'>";
    $output_list .= "<thead><tr><th colspan=3>Öffentliche Challenges</th></tr></thead>";
    $output_list .= $td_list['all'];
    $output_list .= "</table>";
}

if ($_POST['ajax'] == true) {
    echo $output_list;
}