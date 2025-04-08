<?php
include ('../../../ssi_form2/ssiForm.inc.php');

$host = $_POST ['ftp_host'];
$username = $_POST ['ftp_user'];
$password = $_POST ['ftp_password'];
$path = $_POST ['ftp_path'];
$page_id = $_SESSION ['smart_page_id'];

$ftpc = ftp_connect ( $host );
$ftpr = ftp_login ( $ftpc, $username, $password );

if ($ftpr) {
	echo "ok";
	$GLOBALS['mysqli']->query ( "UPDATE tbl_domain SET ftp_host = '$host', ftp_user = '$username', ftp_password = '$password', ftp_path = '$path' WHERE page_id = '$page_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
}

