<?php
/*
 * Confirm Account
 * mm@ssi.at - 18.07.2011
 */
include_once ('../config.inc.php');

$verify_key = $GLOBALS['mysqli']->real_escape_string ( $_GET ['verify_key'] );

$query = $GLOBALS['mysqli']->query ( "SELECT * from client WHERE verify_key = '$verify_key' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );

if ($array ['client_id'] && $array ['hp_inside'] == '0') {
	$message = $strAfterConfirmMsgOk;
	// Activate User
	$GLOBALS['mysqli']->query ( "UPDATE client SET hp_inside = '1' WHERE verify_key = '$verify_key'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	;
} else if ($array ['client_id'])
	$message = $strAfterConfirmMsgActive;
else
	$message = $strAfterConfirmMsgError;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?=$_SERVER["HTTP_HOST"]?></title>
<style type="text/css">
body {
	font-family: arial
}
</style>
</head>
<body>
	<br>
	<br>
	<br>
	<div align=center>

<?
echo $message;
echo "<br><br>";
echo "<a href = 'http://{$_SERVER["HTTP_HOST"]}'>Zurück zur Startseite</a>";
?>
</div>
</body>
</html>
