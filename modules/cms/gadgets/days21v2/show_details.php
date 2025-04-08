<?php
$ajax = $_POST['ajax'];

if ($ajax) {
	include ('../config.php');
	include_once ('mysql_days21.inc.php');
	include_once ('function.php');
	$challenge_id = $_POST['id'];
	$_SESSION['challenge_id'] = $challenge_id;
	
	// Details der Challenge
	$sql = $GLOBALS['mysqli']->query ( "
	SELECT DATE_FORMAT(start_date,'%Y-%m-%d') start_date, name, firstname, secondname, description, target, result, comment_better_way, cancel_reasion, difficulty
		FROM $db_smart.21_groups t1, ssi_company.user2company t2 
			WHERE t1.user_id = t2.user_id 
			AND  challenge_id = $challenge_id " ) or die (mysqli_error());
	$array = mysqli_fetch_array ( $sql );
	$name = $array['name'];
	$firstname = $array['firstname'];
	$secondname = $array['secondname'];
	$user_name = $firstname." ".$secondname;
	$description = nl2br ( chop ( $array['description'] ) );
	$start_date = $array['start_date'];
	$target = nl2br ( chop ( $array['target'] ) );
	$result = nl2br ( chop ( $array['result'] ) );
	$break = nl2br ( chop ( $array['comment_better_way'] ) );
	$cancel_reasion = $array['cancel_reasion'];
	$difficulty = $array_success[$array['difficulty']];
	$title = "<div style='font-size:18px;'>$name<br></div>";
	
	date_default_timezone_set('Europe/Berlin');
	$datetime1 = date_create($start_date);
	$datetime2 = date_create(null);
	$interval = date_diff($datetime1, $datetime2);
	$start_date = $interval->format('%a');
	if ($start_date == 1 ) $start_date = "Seit $start_date Tag";
	else $start_date = "Seit $start_date Tagen";
	
	$user_info  = "<div style='font-size:12px; color:gray;'>$start_date von <b><span class=user>$user_name</span></b></div>";
	
}

$output_detail = '';

// date_format(action_date, '%e.%c.%Y') action_date,
// Details Kommentare
$sql = $GLOBALS['mysqli']->query ( "SELECT action_date, comment, difficulty, session_id from $db_smart.21_sessions WHERE group_id = '$challenge_id' AND comment != '' order by action_date desc " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
while ( $array = mysqli_fetch_array ( $sql ) ) {
	$session_id = $array['session_id'];
	$difficulty_session = $array_success[$array['difficulty']];
	$comment .= "<font color=gray style='font-size:10px;'>" . $array['action_date'] . "</font><br>";
	if ($difficulty_session)
		$comment .= "Einstufung: $difficulty_session<br>";
	$comment .= $array['comment'] . "<br>";
	$comment .= user_comment_bar ( $session_id, 'comment' );
	$comment .= "<br>";
}
$count_comment = mysqli_num_rows ( $sql );


if ($description) {
	$tab1_title = "Beschreibung";
	$tab1_text = "$description<br>";
	$tab1_text .= user_comment_bar ( $challenge_id, 'challenge' );
}

if ($comment) {
	$tab2_title = "Zwischenberichte <div class='ui mini green label'>$count_comment</div> ";
	$tab2_text = "<div style='max-height:450px; overflow: auto;'>$comment</div>";
}

if ($target) {
	$tab3_title = "Ziel";
	$tab3_text = $target;
	$tab3_text .= user_comment_bar ( $challenge_id, 'target' );
}

if ($result) {
	$tab4_title = "Abschlussbereicht";
	if ($difficulty)
		$tab4_text = "Persönliche Einstufung der Challenge: <b>$difficulty</b><br><hr>";
	$tab4_text .= $result;
	$tab4_text .= user_comment_bar ( $challenge_id, 'result' );
}

if ($break) {
	$tab4_title = "Abgebrochen";
	if ($cancel_reasion)
		$tab4_text .= "<b>Warum vorzeitig beendet:</b><br>{$array_chancel[$cancel_reasion]}<br><br>";
	if ($break)
		$tab4_text .= "<b>Was ist besser machen kann:</b><br>$break";
	$tab4_text .= user_comment_bar ( $challenge_id, 'break' );
}
$output_detail .= "$title";
$output_detail .= "$user_info";
$output_detail .= "<hr>";

$output_detail .= "<div class='ui top attached tabular menu' >";
if ($tab1_title)
	$output_detail .= "<a class='active item' data-tab='first$challenge_id' style='font-size:12px;'>$tab1_title</a>";
if ($tab2_title)
	$output_detail .= "<a class='item' data-tab='second$challenge_id' style='font-size:12px;'>$tab2_title</a>";
if ($tab3_title)
	$output_detail .= "<a class='item' data-tab='third$challenge_id' style='font-size:12px;'>$tab3_title</a>";
if ($tab4_title)
	$output_detail .= "<a class='item' data-tab='four$challenge_id' style='font-size:12px;'>$tab4_title</a>";
if ($tab5_title)
	$output_detail .= "<a class='item' data-tab='five$challenge_id' style='font-size:12px;'>$tab5_title</a>";
$output_detail .= "</div>";

if ($tab1_text)
	$output_detail .= "<div class='ui bottom attached active tab segment' data-tab='first$challenge_id'><div class=tab_content>$tab1_text</div></div>";
if ($tab2_text)
	$output_detail .= "<div class='ui bottom attached tab segment' data-tab='second$challenge_id'><div class=tab_content>$tab2_text</div></div>";
if ($tab3_text)
	$output_detail .= "<div class='ui bottom attached tab segment' data-tab='third$challenge_id'><div class=tab_content>$tab3_text</div></div>";
if ($tab4_text)
	$output_detail .= "<div class='ui bottom attached tab segment' data-tab='four$challenge_id'><div class=tab_content>$tab4_text</div></div>";
if ($tab5_text)
	$output_detail .= "<div class='ui bottom attached tab segment' data-tab='five$challenge_id'><div class=tab_content>$tab5_text</div></div>";

if ($ajax) {
	// echo "<link rel='stylesheet' type='text/css' href='gadgets/days21/share_detail.css'>";
	// echo "<script type=\"text/javascript\" src=\"gadgets/days21/js/jquery.autosize.js\"></script>";
	echo "<script type='text/javascript' src='$path21/js/detail.js'></script>";
	echo "$output_detail";
}
