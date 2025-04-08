<?php
//Wurde ersetzt durch dynamischen Austausch beim erzeugen der Seite ! 
exit;
//Change meta-tag for Detail-Page

// jquery call
// $.ajax({
// 	url : 'gadgets/paneon_faq/get_meta.php',
// 	global : false,
// 	type : 'POST',
// 	dataType :'script',
// 	data : ({'faq_id': '$faq_id' }),
// });


//Aus der Datenbank auslesen und Werte über Jquery in der Seite austauschen
require_once ('../config.php');
$faq_id = $_POST ['faq_id'];

if ($faq_id) {
	$query2 = $GLOBALS ['mysqli']->query ( "SELECT * FROM ssi_paneon.faq LEFT JOIN ssi_paneon.faq2tag ON faq.faq_id = faq2tag.faq_id WHERE faq.faq_id = '$faq_id' " );
	
	$array = mysqli_fetch_array ( $query2 );
	//Ändern der meta-tags
	echo "$('meta[property=\"og:title\"]').attr('content', '{$array ['title']}');\n";
	echo "$('meta[property=\"og:description\"]').attr('content', '{$array ['problem']}');\n";
	echo "$('meta[property=\"og:image\"]').attr('content', '{$array ['image']}');\n";
	
	//Ändern von Title
	echo "$(document).attr('title', '{$array ['title']}');\n";
}