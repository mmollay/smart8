<?php
// format Price
function number_mysql2german($wert) {
	$euro = sprintf ( "%01.2f", $wert );
	return number_format ( $euro, 2, ',', '.' ) . " €";
}

function fu_log_error($error_msg) {
	
	// call IP - from Client
	if (! isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
		$client_ip = $_SERVER ['REMOTE_ADDR'];
	} else {
		$client_ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
	}
	
	$path = $_SERVER ['PHP_SELF'];
	$file = basename ( $path );
	$GLOBALS['mysqli']->query ( "INSERT INTO log_error SET
	ip = '{$client_ip}',  
	msg = '$error_msg',
	domain ='{$_SERVER["HTTP_HOST"]}'
	
	" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
}