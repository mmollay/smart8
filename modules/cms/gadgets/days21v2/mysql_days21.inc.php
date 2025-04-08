<?php
session_start ();
$db_smart = 'ssi_smart1';

date_default_timezone_set ( 'Europe/Vienna' );

if (isset($_SESSION['path21'])) $path21 = $_SESSION['path21'];
else  $path21 = "gadgets/days21";

$userbar_id = $_SESSION['userbar_id'];

//Abrage Superuser 
$query = $GLOBALS['mysqli']->query("SELECT * FROM ssi_company.user2company WHERE user_id = '$userbar_id' and superuser = 1 ") or die(mysqli_error());
$superuser = mysqli_num_rows($query);

$array_chancel = array(
		'no_hard' => 'Zu viel vorgenommen',
		'no_time' => 'Zu wenig Zeit',
		'no_discipline' => 'Zu wenig Diszipliniert',
		'other_reason' => 'Anderer Grund..'
);

//array "Wie ist es dir gegangen"
$array_success = array(
		1 => "<label class='ui label mini'>Sehr leicht</label>",
		2 => "<label class='ui label mini'>Leicht</label>",
		3 => "<label class='ui label mini'>Mittel</label>",
		4 => "<label class='ui label mini'>Schwer</label>",
		5 => "<label class='ui label mini'>Sehr schwer</label>"
);

$array_success2 = array(
		1 => "Sehr leicht",
		2 => "Leicht",
		3 => "Mittel",
		4 => "Schwer",
		5 => "Sehr schwer"
);

// Gemeinsame Nutzung der Einstellungen fuer Admin und Publicbereich
$count_size = 10;
$date = date ( 'Y-m-d' );
$_SESSION['add_mysql'] = '';
$_SESSION['add_mysql_list'] = '';

// ab X Tagen wird der Prozess auf inaktive Challenge gesetzt
$cfg_count_inactive = 4;
$cfg_max_length = 45;
$cfg_max_length_desc = 200;

$_SESSION['show_all'] = $_SESSION['show_all'] ?? '';

// Wenn public ist wird immer alles reguläre angezeigt
if ($userbar_id) {
	// Show All (exept private from other) or Self
	if ($_SESSION['show_all'] != 'checked' ) {
		$_SESSION['add_mysql'] .= "AND ( t2.user_id = '$userbar_id'  or (view_modus = 'public' or  view_modus = 1 )) ";
	} else {
		$_SESSION['add_mysql'] .= "AND t2.user_id = '$userbar_id' ";
	}
} else {
	$_SESSION['add_mysql'] .= "AND (view_modus = 'public' or  view_modus = 1 ) ";
}

/*
 * SearchText
 */
if ($_SESSION['list_search'] ?? '' ) {
	$_SESSION['add_mysql'] .= "AND MATCH(name,description) AGAINST ('{$_SESSION['list_search']}*' IN BOOLEAN MODE)";
}
else $_SESSION['list_search']='';

// Anzahl auslesen der inativen Prozesse bei mir als X
$count_action_unchecked = "(SELECT COUNT(*) FROM $db_smart.21_sessions WHERE group_id = t2.challenge_id AND action = '' AND now() >= action_date) ";

// nicht gewählt wurde
if (! $_SESSION['select_action'])
	$_SESSION['select_action'] = 'list_all';

// bei erneuten Versuch wird Feld nicht angezeigt
if ($_SESSION['select_action'] == 'list_all') {
	$_SESSION['add_mysql_list'] .= "AND !(SELECT COUNT(*) FROM $db_smart.21_groups WHERE t2.challenge_id = parent_id AND t2.user_id = '$userbar_id' )";
//	$_SESSION['add_mysql_list'] .= "AND !(SELECT COUNT(*) FROM 21_groups WHERE t2.challenge_id = parent_id )";
} elseif ($_SESSION['select_action'] == 'list_new') {
	$_SESSION['add_mysql_list'] .= "AND start_date > NOW() ";
} elseif ($_SESSION['select_action'] == 'list_failed') {
	$_SESSION['add_mysql_list'] .= "AND status = 'fail' AND !(SELECT COUNT(*) FROM $db_smart.21_groups WHERE t2.challenge_id = parent_id )"; 
} elseif ($_SESSION['select_action'] == 'list_success') {
	$_SESSION['add_mysql_list'] .= "AND status = 'success' ";
} elseif ($_SESSION['select_action'] == 'list_running') {
	$_SESSION['add_mysql_list'] .= "AND status = '' AND DATEDIFF(NOW(),start_date)<=21 AND start_date < NOW() AND $count_action_unchecked < $cfg_count_inactive";
} elseif ($_SESSION['select_action'] == 'list_unconfirmed') {
	$_SESSION['add_mysql_list'] .= "AND status = '' AND DATEDIFF(NOW(),start_date)>21";
} elseif ($_SESSION['select_action'] == 'list_inactive') {
	$_SESSION['add_mysql_list'] .= "AND status = '' AND DATEDIFF(NOW(),start_date)<=21 AND $count_action_unchecked >= $cfg_count_inactive";
}

$add_mysql = $_SESSION['add_mysql']. $_SESSION['add_mysql_list'] ;