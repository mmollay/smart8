<?php
include (__DIR__ . '/../f_config.php');
$search_text = $GLOBALS['mysqli']->real_escape_string($_GET['q']);

$return_arr = array();

// Boolean mode
// if (str_word_count ( $search_text, 0, 'äüöÄÜÖß' ) > 1) {
// 	$search_text = "+" . preg_replace ( '/ (\w+)/', ' +$1', $search_text );
// }
//$search_mode = "WHERE MATCH(description) AGAINST('$search_text' IN BOOLEAN MODE)";

// like mode
$search_mode = "WHERE description LIKE '%$search_text%'";

if ($search_text) {
	$query = $GLOBALS['mysqli']->query("
		SELECT bill_id, description, date_create,accounts.title title,name  
			FROM issues 
				LEFT JOIN accounts ON account = account_id AND `option` = 'out'
				LEFT JOIN issues_group ON issues_group.issues_group_id = issues.client_id 
				$search_mode
					order by bill_id desc") or die(mysqli_error($GLOBALS['mysqli']));
	while ($array = mysqli_fetch_array($query)) {
		$row_array['bill_id'] = $array['bill_id'];
		$row_array['title'] = $array['description'];
		$row_array['description'] = $array['date_create'] . " - " . $array['title'];

		if ($array['name'])
			$row_array['description'] .= " - " . $array['name'];

		array_push($return_arr, $row_array);
	}
}

$arr = array('success' => true, 'results' => $return_arr);

echo json_encode($arr);