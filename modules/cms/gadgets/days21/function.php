<?php
// Anzahl der Likes ermitteln user spezifisch like definieren
function like_button_count($id, $element) {
	global $db_smart; 
	$path21 = $GLOBALS['path21'];
	// $user_name = "<div class=user_name>Martin</div>";
	$userbar_id = $_SESSION['userbar_id'];
	
	// count Cool own
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_like WHERE element_id = '$id' AND element = '$element' AND user_id ='$user_id' " );
	$count = mysqli_num_rows ( $query );
	
	// count Cool all together
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_like INNER JOIN ssi_company.user2company t2 ON 21_like.user_id = t2.user_id WHERE element_id = '$id' AND element = '$element' " );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$first_name = $array['firstname'];
		$second_name = $array['secondname'];
		$user_id = $array['user_id'];
		if ($user_id == $userbar_id)
			$name .= "Dir<br>";
		else
			$name .= "$first_name $second_name<br>";
	}
	
	$count_cool_all_together = mysqli_num_rows ( $query );
	
	if ($count_cool_all_together >= 1)
		$count_cool_all_together = "<b>$count_cool_all_together</b>";
	
	if ($count)
		$icon = 'heart red';
	else
		$icon = 'empty heart';
	
	if (! $count)
		$title_add = '+ Diesen Beitrag ein <b>Herz</b> geben<br>';
		
		// if($name)
	$set_title = "data-html='$title_add $name' data-position='bottom center' data-variation='small inverted' ";
	// else $set_title = "data-html='Für diesen Beitrag ein <b>Herz</b> geben<br>$name' data-position='bottom center' data-variation='small inverted'" ;
	
	return "<div class='ui mini basic white icon button tooltip' $set_title><i class='$icon icon'></i>&nbsp;$count_cool_all_together</div>";
}

// Ausgabe des COOL - Button und deren Kommentare
function like_bar($id, $element) {
	$count = like_button_count ( $id, $element );
	return "<span class='button_user_like' title='' onclick='set_button_cool(\"$id\",\"$element\")'><span class=like_button_count_{$element} id=$id>$count</span></span>";
}

// Anzahl der Kommentare ermitteln
function comment_button_count($id, $element) {
	global $db_smart;
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_comment WHERE element_id = '$id' AND element='$element' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$count = mysqli_num_rows ( $query );
	return $count;
}

// Button Comment
function comment_bar($id, $element, $view = '', $link = '') {
	$count = comment_button_count ( $id, $element );
	
	if ($count == '0')
		$count_detail = '';
	
	$link .= "set_form_textarea(\"$id\",\"$element\");";
	
	if ($view == 'icon') {
		return "<span class='button_user_comment' onclick=show_details('$id')><div class='ui icon mini basic button tooltip' title='Beitrag kommentieren'><i class='comment outline icon'></i><span class=comment_button_count_{$element} id=$id> $count</span></div></span>";
	} else
		return "<span class='button_user_comment' onclick='$link'><div class='ui icon mini basic button'><i class='comment outline icon'></i> Beitrag kommentieren <span class=comment_button_count_{$element} id=$id> $count_detail</span></div></span>";
}

/*
 * Darstellung des Eintrages
 */
function user_comment_detail($array) {
	$comment_id = $array['comment_id'];
	$date = $array['timestamp'];
	// echo $_SESSION['user_id'];
	if ($_SESSION['userbar_id'] == $array['user_id']) {
		$button_edit_button = "<i class='ui large icon edit link' onclick='edit_own_comment(\"$comment_id\")'></i>";
		$button_edit_button .= "<i class='ui large icon remove link' onclick='rm_own_comment(\"$comment_id\")'></i>";
	} else {
		$button_edit_button = "";
	}
	
	$button_edit = "<div id='$comment_id' class='comment_edit_button'> $button_edit_button</div>";
	
	$comment = $array['comment'];
	$comment = chop ( $comment );
	$comment = trim ( $comment );
	$comment = preg_replace ( '/(\r?\n){3,}/', '$1$1', $comment );
	$comment_list .= "<div id='$comment_id' class='container_comment_div' >";
	$comment_list .= "<b>" . $array['firstname'] . " " . $array['secondname'] . "</b>";
	$comment_list .= "<span class=comment_date>" . $date . "</span>";
	$comment_list .= "$button_edit<br>";
	$comment_list .= "<div id='comment_text_$comment_id'>" . nl2br ( $comment ) . "</div><br>";
	$comment_list .= "</div>";
	return $comment_list;
}

// Kommentare der User
function comment_list($id, $element) {
	global $db_smart;
	$query = $GLOBALS['mysqli']->query ( "SELECT DATE_FORMAT(t1.timestamp,'%d.%m.%Y') timestamp, firstname, secondname, comment, comment_id, t2.user_id from ssi_smart1.21_comment t1 LEFT JOIN ssi_company.user2company t2 ON t1.user_id = t2.user_id WHERE element_id = '1' AND element='comment' ORDER BY comment_id desc" );
	while ( $array = mysqli_fetch_array ( $query ) ) {
		$comment_list .= user_comment_detail ( $array );
	}
	$button_comment_text .= "<div class='container_form_usercomment_{$element}_{$id}'></div>";
	$button_comment_text .= "<div class='comment_list{$element}_{$id}'>$comment_list</div>";
	return $button_comment_text;
}

// Gesamte Bar - mit Buttons und Kommentaren
function user_comment_bar($id, $element) {
	$comment .= "<div class='comment_container'>";
	$comment .= "<div class=like_button_bar>";
	$comment .= like_bar ( $id, $element );
	$comment .= comment_bar ( $id, $element );
	$comment .= "</div>";
	$comment .= comment_list ( $id, $element );
	$comment .= "</div>";
	return $comment;
}

// Progressbar erzeugen
function call_progress_bar($challenge_id) {
	global $db_smart;
	$progress_field = '';
	$progress_count = '';
	$query2 = $GLOBALS['mysqli']->query ( "SELECT * FROM $db_smart.21_sessions, $db_smart.21_groups WHERE group_id = challenge_id AND group_id = '$challenge_id' AND action_date <= NOW() AND (failed_date > action_date OR failed_date = '') order by action_date" );
	while ( $array_progress = mysqli_fetch_array ( $query2 ) ) {
		$progress_count ++;
		$progress_single = $array_progress['action'];
		if ($array_progress['action'] == 'success') {
			$progress_field .= "<div class=progressbar_success></div>";
		} elseif ($array_progress['action'] == 'fail') {
			$progress_field .= "<div class=progressbar_fail></div>";
		} else {
			$progress_field .= "<div class=progressbar_unknown></div>";
		}
	}
	
	// Progressanzeige beim ersten Tag
	if ($progress_count == 1) {
		if ($progress_single == 'success') {
			$progress_field = "<div class=progressbar_success_single></div>";
		} elseif ($progress_single == 'fail') {
			$progress_field = "<div class=progressbar_fail_single></div>";
		} else
			$progress_field = "<div class=progressbar_unknown_single></div>";
		return "<div class=progressbar_single>$progress_field</div>";
	} else
		return "<div class=progressbar>$progress_field</div>";
}

