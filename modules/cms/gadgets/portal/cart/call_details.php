<?php
include_once ('../config.inc.php');


if ($_POST['code']) {
	$id = $_POST['code'];
} else {
	$id = $_POST['id'];
}

$query = $GLOBALS['mysqli']->query ( "SELECT * FROM article_temp WHERE temp_id = '$id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );

/*
 * OEGT - Show Inside- Details
 */
if ($_SESSION['oegt_user'] or $_SESSION['abo']) {
	$text = $array['internet_inside_text'];
	$title = $array['internet_inside_title'];
	$TrackingCode = $TrackingCode['31'];
} else {
	$text = $array['internet_text'];
	$title = $array['internet_title'];
}

if ($set_static == true) {
	$text = preg_replace ( "/\/users\/user$user_id\/explorer\/$page_id\//", "/explorer/", $text );
	$text = preg_replace ( "/\/users\/user$user_id\/explorer\/143\//", "/explorer/", $text );
	$text = preg_replace ( "/\/smart_users\/ssi\/user$user_id\/explorer\/$page_id\//", "/explorer/", $text );
}

if (! $text)
	$text = $array['art_text'];
if (! $title)
	$title = $array['art_title'];

// if ($TrackingCode and ! $_SESSION['admin_modus']) {
// 	echo "
// 	<script>
// 	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
// 	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
// 	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
// 	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

// 	ga('create', '$TrackingCode', 'auto');
// 	ga('send', {
// 	'hitType': 'pageview',
// 	'page': '{$_SERVER['PHP_SELF']}?id=$id',
// 	'title': '$title'
// 	});
// 	</script>
// ";
// }

if ($_POST['code'])
	echo "<button onclick=\"javascript:location.href='?' \" class='button ui'>Übersicht</button>";

echo "<h3 class='ui block header'>$title</h3>";
echo $text;