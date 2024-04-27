<?php
// mm@ssi.at am 11.03.2017
require ("../config.inc.php");
$temp_id = $GLOBALS['mysqli']->real_escape_string ( $_POST['temp_id'] );
if (! $temp_id) {
	echo "alert('kein Artikel gewählt');";
	return;
}

//$GLOBALS['mysqli']->query ('SET NAMES utf8');
$query = $GLOBALS['mysqli']->query ( "SELECT art_nr,art_title,format,count,art_text,account,netto FROM article_temp WHERE temp_id = '$temp_id' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$array = mysqli_fetch_array ( $query );

foreach ( $array as $key => $value ) {
	
	//$value = str_replace ( "\n", "\\n", $value ); // brauchte es wenn Zeilenumbrüche verwendet werden	
	
	if ($key == "account") {
		echo "$('#dropdown_$key').dropdown('set selected','$value');";		
	}
	else {
		//$value = htmlentities($value);
		$val = json_encode($value);
		echo "$('#$key').val($val);";
	}
	
}