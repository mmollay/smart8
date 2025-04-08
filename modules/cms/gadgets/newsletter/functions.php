<?php
// Check - sind noch genug Codes vorhanden sofern eine Promotion an das Formular angebunden ist
function check_promotion_is_active($db, $camp_key) {
	
	$query = $GLOBALS['mysqli']->query ( "SELECT COUNT(c.promotion_id) count, a.promotion_id promotion_id ,text_promotion_codes_used_up
			FROM $db.formular a
			LEFT JOIN $db.promotion b ON a.promotion_id = b.promotion_id
			LEFT JOIN $db.code c ON c.promotion_id = b.promotion_id
			WHERE camp_key = '$camp_key' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array = mysqli_fetch_array ( $query );
	$text_promotion_codes_used_up = $array['text_promotion_codes_used_up'];
	$count = $array['count'];
	$promotion_id = $array['promotion_id'];
	// Abbrechen wenn keine Promo Promotion läuft - Normales Listbuilding
	if (! $promotion_id) {
		return;
	} elseif (! $count) {
		if (! $text_promotion_codes_used_up)
			$text_promotion_codes_used_up = 'No Promo-Codes';
		return $text_promotion_codes_used_up;
	}
}




function check_promotion_is_active_save($db, $camp_key) {
    // Prepare the SQL statement
    $stmt = $GLOBALS['mysqli']->prepare("SELECT COUNT(c.promotion_id) AS count, a.promotion_id AS promotion_id, text_promotion_codes_used_up
    FROM $db.formular a
    LEFT JOIN $db.promotion b ON a.promotion_id = b.promotion_id
    LEFT JOIN $db.code c ON c.promotion_id = b.promotion_id
    WHERE camp_key = ?");
    // Bind the camp_key parameter to the prepared statement
    $stmt->bind_param("s", $camp_key);
    // Execute the prepared statement
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();
    // Fetch the associative array from the result
    $array = $result->fetch_assoc();
    
    // Abbrechen wenn keine Promo Promotion läuft - Normales Listbuilding
    if (!$array['promotion_id']) {
        return;
    } elseif (empty($array['count'])) {
        return $array['text_promotion_codes_used_up'] ?: 'No Promo-Codes';
    }
}
