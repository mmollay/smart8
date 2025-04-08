<?php
include ("../../smart_form/include_list.php");
$array = call_list ( 'list_search.php', '../config.php' );
echo $array['html'];
echo $array['js'];


// include ('../config.php');

// if (isset ( $_POST['search'] )) {
// 	$host = "localhost";
	
// 	$search_val = $_POST['search_term'];
	
// 	$get_result = $GLOBALS['mysqli']->query ( "select 
// 		a.text text, c.title title 
// 		FROM 
// 			smart_langLayer a
// 				LEFT JOIN smart_layer b ON a.fk_id=b.layer_id
// 			   	LEFT JOIN smart_langSite c ON b.site_id = c.fk_id 
// 				where MATCH(a.text,c.title) AGAINST('$search_val')" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
// 	while ( $row = mysqli_fetch_array ( $get_result ) ) {
// 		$row['text'] = strip_tags ( $row['text'] );
// 		$row['title'] = $row['title'];
// 		$list .= "<div class='ui header'>" . $row['title'] . "<div class='sub header'>" . $row['text'] . "</div></div>";
// 	}
// }

// echo "<div class='ui list'><br>$list</div>";