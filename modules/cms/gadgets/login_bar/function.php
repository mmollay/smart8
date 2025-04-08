<?php
function get_verify_form_user2() {
	$query_verify = $GLOBALS['mysqli']->query ( "SELECT verify_key FROM ssi_company.user2company WHERE user_id = '{$_SESSION['user_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$array_verify = mysqli_fetch_array ( $query_verify );
	return $array_verify['verify_key'];
}