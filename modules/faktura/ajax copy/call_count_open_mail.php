<?php
/*
 * Martin Mollay martin@ssi.at am 04.11.2011
 * CALL Count form Mails with no jet sendet to the User - AJAX (Request to send_form.php after send Mails
 */
require ("../config.inc.php");

$mysql_query = $GLOBALS['mysqli']->query ( "
		SELECT bill_id FROM bills WHERE remind_level = 0
		AND company_id = '{$_SESSION['faktura_company_id']}'
		AND date_booking = '0000-00-00'
		AND date_storno = '0000-00-00'
		AND email != ''
		" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
echo mysqli_num_rows ( $mysql_query );
?>