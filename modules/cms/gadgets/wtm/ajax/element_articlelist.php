<?php
session_start ();
include ("element_userbar.php"); // Es wird direkt ein ECHO abgerufen
if ($_POST['group_id']) $_SESSION['group_id'] = $_POST['group_id'];  

if (! $_SESSION['group_id']) {
	echo "Gruppe ist nicht definiert";
	exit ();
}

$menu_style = 'small';

// Archiv
if ($_SESSION['group_id'] == '12') {
	$set_min_year = '2003';
	$menu_style = 'mini';
	$add_field = "<a href='http://anno.onb.ac.at/cgi-content/anno-plus?aid=wtm' target='neu' class='tab_year item'>1946 – 1914</a>";
} // Themenhefte
elseif ($_SESSION['group_id'] == '15') {
	$set_min_year = '2010';
}


if ($set_min_year) {
	if ($_SESSION['filter_year'] == '')
		$addClass1 = 'active';
	$item .= "<a onclick=filter_year('') class='tab_year $addClass1 item' id='all'>Alle</a>";
	date_default_timezone_set ( 'Europe/Berlin' );
	for($year = date ( Y ); $year >= $set_min_year; $year --) {
		$array_year[$year] = $year;
		if ($year == $_SESSION['filter_year'])
			$addClass = 'active';
		else
			$addClass = '';
		$item .= "<a onclick=filter_year($year) class='tab_year $addClass item' id='$year'>$year</a>";
	}
	echo "<div class='tablet' style='padding-bottom:10px;'><div class='ui secondary pointing $menu_style menu' >$item$add_field</div></div>";
}
date_default_timezone_set('Europe/Vienna');
$update_actuall_nr = date('Y', strtotime('-1 year')).DATE('m')."00";


//korrigiert falsch eingetragene Werte
$GLOBALS['mysqli']->query ("UPDATE article_temp SET company_id = 31 WHERE account=65 ") or die(mysqli_error());
//setzt Artikel nach einem Jahr auf "free"
$GLOBALS['mysqli']->query ("UPDATE article_temp SET free = 1 WHERE art_nr < '$update_actuall_nr' and internet_show = 1 and company_id=31 ") or die(mysqli_error());

include ("../../../smart_form/include_list.php");
$array = call_list ( '../list/article.php', '../mysql.php' );
echo $array['html'];
echo $array['js'];