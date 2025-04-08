<?php 
// Holt die Kampange von Newsletter-System
function call_array_formular($db) {
	
	// $db -> Datenbank vom SSI-Newsletter
	if ($_SESSION['user_id']) {
		$mysql_group_query = $GLOBALS['mysqli']->query ( "
						SELECT camp_key, matchcode,title,from_name,from_email
						FROM $db.formular a
							LEFT JOIN $db.promotion b ON a.promotion_id = b.promotion_id
							LEFT JOIN $db.sender e ON a.from_id = e.id
								WHERE a.user_id = '{$_SESSION['user_id']}'
									ORDER BY matchcode  " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
		while ( $mysql_group_fetch = mysqli_fetch_array ( $mysql_group_query ) ) {
			$camp_id = $mysql_group_fetch['camp_key'];
			$from_email = $mysql_group_fetch['from_email'];
			$from_name = $mysql_group_fetch['from_name'];
			// $matchcode = " <div class='ui label'>$from_name ($from_email)</div>";
			$matchcode = $mysql_group_fetch['matchcode'];
			if ($mysql_group_fetch['title'])
				$matchcode .= " (" . $mysql_group_fetch['title'] . ")";
			$array[$camp_id] = "$matchcode";
		}
	}
	return $array;
}
