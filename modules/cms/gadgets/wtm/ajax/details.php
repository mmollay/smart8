<?php
include ('../mysql.php');

if ($_POST['code']) {
	$id = $_POST['code'];
} else {
	$id = $_POST['update_id'];
}
mysql_set_charset('utf8');
$GLOBALS['mysqli']->query('SET NAMES utf8');
$query = $GLOBALS['mysqli']->query ( "SELECT * FROM article_temp WHERE temp_id = '$id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );

$text = $array['internet_text'];
$title = $array['internet_title'];

if ($set_static == true) {
	$text = preg_replace ( "/\/users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $text );
	$text = preg_replace ( "/\/users\/user$user_id\/explorer\/143\//", "/explorer/", $text );
	$text = preg_replace ( "/\/smart_users\/ssi\/user$user_id\/explorer\/$page_id\//", "/explorer/", $text );
}

if (! $text)
	$text = $array['art_text'];
if (! $title)
	$title = $array['art_title'];

//if ($_POST['code'])
//	echo "<button onclick=\"javascript:location.href='?' \" class='button ui'>Übersicht</button><br><br>";

echo "<div align=left>";
echo "<h3 class='ui block header'>$title</h3>";
echo $text;
echo "</div>";