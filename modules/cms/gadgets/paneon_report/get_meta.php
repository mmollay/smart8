<?php
//Wurde ersetzt durch dynamischen Austausch beim erzeugen der Seite ! 
exit;
//Change meta-tag for Detail-Page

// jquery call
// $.ajax({
// 	url : 'gadgets/paneon_report/get_meta.php',
// 	global : false,
// 	type : 'POST',
// 	dataType :'script',
// 	data : ({'report_id': '$report_id' }),
// });


//Aus der Datenbank auslesen und Werte über Jquery in der Seite austauschen
require_once ('../config.php');
$report_id = $_POST ['report_id'];

if ($report_id) {
	$query2 = $GLOBALS ['mysqli']->query ( "SELECT * FROM ssi_paneon.report LEFT JOIN ssi_paneon.report2tag ON report.report_id = report2tag.report_id WHERE report.report_id = '$report_id' " );
	$array = mysqli_fetch_array ( $query2 );
	//Ändern der meta-tags
	echo "$('meta[property=\"og:title\"]').attr('content', '{$array ['title']}');\n";
	echo "$('meta[property=\"og:description\"]').attr('content', '{$array ['problem']}');\n";
	echo "$('meta[property=\"og:image\"]').attr('content', '{$array ['image']}');\n";
	
	//Ändern von Title
	echo "$(document).attr('title', '{$array ['title']}');\n";
}