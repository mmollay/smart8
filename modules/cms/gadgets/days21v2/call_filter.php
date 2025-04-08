<?php

// Einbinden der Konfigurationsdateien und Hilfsbibliotheken
include ('../config.php');
include_once ('mysql_days21.inc.php');

// Initialisierung der Variablen
$add_mysql = $_SESSION['add_mysql'];
$add_fail_mysql = "AND !(SELECT COUNT(*) FROM $db_smart.21_groups WHERE t2.challenge_id = parent_id) ";

// Funktion zur Ausführung von SQL-Abfragen und Zählung der Ergebnisse
function executeQueryAndCount($query)
{
    $result = $GLOBALS['mysqli']->query($query) or die(mysqli_error($GLOBALS['mysqli']));
    return mysqli_num_rows($result);
}

// Verschiedene SQL-Abfragen für verschiedene Status
$query_fail = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE 1 AND status = 'fail' $add_fail_mysql $add_mysql";
$query_all = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE 1 $add_mysql AND !(SELECT COUNT(*) FROM $db_smart.21_groups WHERE t2.challenge_id = parent_id)";
$query_success = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE status = 'success' $add_mysql";
$query_running = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE status = '' AND DATEDIFF(NOW(),start_date)<=21 AND start_date < NOW() AND $count_action_unchecked < $cfg_count_inactive $add_mysql";
$query_new = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE status = '' AND start_date > now() $add_mysql";
$query_unconfirmed = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE status = '' AND DATEDIFF(NOW(),start_date)>21 $add_mysql";
$query_inactive = "SELECT challenge_id FROM $db_smart.21_groups t2 WHERE status = '' AND DATEDIFF(NOW(),start_date)<=21 AND $count_action_unchecked >= $cfg_count_inactive $add_mysql";

// Ausführung der SQL-Abfragen und Zählung der Ergebnisse
$count_fail = executeQueryAndCount($query_fail);
$count_all = executeQueryAndCount($query_all);
$count_success = executeQueryAndCount($query_success);
$count_running = executeQueryAndCount($query_running);
$count_new = executeQueryAndCount($query_new);
$count_unconfirmed = executeQueryAndCount($query_unconfirmed);
$count_inactive = executeQueryAndCount($query_inactive);

// HTML-Formatierung für die Anzeige der Zählungen
$style_count_fail = "<span class=days21_counter_fail>$count_fail</span>";
$style_count_all = "<span class=days21_counter_all>$count_all</span>";
$style_count_success = "<span class=days21_counter_success>$count_success</span>";
$style_count_running = "<span class=days21_counter_running>$count_running</span>";
$style_count_new = "<span class=days21_counter_new>$count_new</span>";
$style_count_unconfirmed = "<span class=days21_counter_unconfirmed>$count_unconfirmed</span>";
$style_count_inactive = "<span class=days21_counter_unconfirmed>$count_inactive</span>";

// Weitere Logik und HTML-Ausgabe (wurde nicht verändert, da sie bereits gut strukturiert ist)
// ...

/*
 * Output
 */
$check[$_SESSION['select_action']] = 'active selected';

$array_filter['list_all'] = "$style_count_all Alle Challenges";
$array_filter['list_new'] = "$style_count_new Vorbereitet";
$array_filter['list_running'] = "$style_count_running Aktiv";
$array_filter['list_success'] = "$style_count_success Erfolgreich";
$array_filter['list_failed'] = "$style_count_fail Abgebrochen";
$array_filter['list_inactive'] = "$style_count_inactive Inaktiv";
$array_filter['list_unconfirmed'] = "$style_count_unconfirmed Abgelaufen";

// Title wenn eine Filter gewählt wurde
$check_title = $array_filter[$_SESSION['select_action']];
if (! $check_title)
    $check_title = 'Filter';

// if ($_SESSION['user_id']) {
$output_button_start = "<div class='ui blue button tooltip icon' onclick='call_modal_form()' title='Klicke hier und starte mit deiner Challenge!'><i class='rocket icon'></i> Neue Challenge</div>";
// }

$output_filter = "&nbsp;<div class='ui labeled tiny icon top right pointing dropdown button' style='border:1px solid silver; box-shadow: 1px 1px 1px #EEE;' >
  <i class='filter icon'></i>
  <span class='text'>$check_title</span>
  <div class='menu'>
    <div class='header'><i class='tags icon'></i> gefiltert nach </div>";
$output_filter .= "<div class='item select_action tooltip {$check['list_all']}' id='list_all' title='Alle Challenges'>" . $array_filter['list_all'] . "</div>";
$output_filter .= "<div class='divider'></div>";
$output_filter .= "<div class='item select_action tooltip " . ($check['list_running'] ?? '') . "' id='list_running' title='Laufende Challenges'>" . $array_filter['list_running'] . "</div>";
if ($count_new)
    $output_filter .= "<div class='item select_action tooltip " . ($check['list_new'] ?? '') . "' id='list_new' title='Künftinge Challenges'>" . $array_filter['list_new'] . "</div>";
if ($count_success)
    $output_filter .= "<div class='item select_action tooltip " . ($check['list_success'] ?? '') . "' id='list_success' title='Erfolgreiche Challenges'>" . $array_filter['list_success'] . "</div>";
if ($count_fail)
    $output_filter .= "<div class='item select_action tooltip " . ($check['list_failed'] ?? '') . "' id='list_failed' title='Abgebrochene Challenges'>" . $array_filter['list_failed'] . "</div>";
if ($count_inactive or $count_unconfirmed)
    $output_filter .= "<div class='divider'></div>";
if ($count_inactive)
    $output_filter .= "<div class='item select_action tooltip " . ($check['list_inactive'] ?? '') . "' id='list_inactive' title='Inaktive Challenges'>" . $array_filter['list_inactive'] . "</div>";
if ($count_unconfirmed)
    $output_filter .= "<div class='item select_action tooltip " . ($check['list_unconfirmed'] ?? '') . "' id='list_unconfirmed' title='Abgelaufene Challenges (unbestätigte Tage)'>" . $array_filter['list_unconfirmed'] . "</div>";
$output_filter .= "</div></div>";

$output_search = "
<div class='ui icon input'>
<input style='border:1px solid silver; box-shadow: 1px 1px 1px #EEE;'  id='list_search' placeholder='Search...' type='text' value='{$_SESSION['list_search']}'>
<i class='search icon'></i>
</div>";

if ($userbar_id) {

    if ($_SESSION['show_all'] == 'checked')
        $checked = 'checked';
    else
        $checked = '';

    $output_checkbox_show = " <div class='ui checkbox'><input id=show_all name='show_all' type='checkbox' $checked><label for='show_all'>nur Meine anzeigen</label></div>";
} // Anzeigen aller User

$output = "<div class='ui grid stackable'>";
$output .= "<div class='twelve wide column'>";
$output .= "<form id=submit name=submit>";
$output .= $output_search;
$output .= $output_filter;
$output .= $output_checkbox_show;
// $output .=$output_button_start;
$output .= "</form>";
$output .= "</div>";
$output .= "<div class='four wide right aligned column'>";
$output .= "$output_button_start";
$output .= "</div>";
$output .= "</div>";
// $output .= "<hr>";

echo $output;