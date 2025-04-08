<?php
include_once ('../../../login/config_main.inc.php');

// TOGGLE for favorite
if ($_POST['toggle']) {
	$GLOBALS['mysqli']->query ( "UPDATE smart_id_site2id_page SET favorite = IF(favorite=1, 0, 1) WHERE site_id = '{$_SESSION['site_id']}' " ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
}

// Call Status
$sql = $GLOBALS['mysqli']->query ( "SELECT favorite FROM smart_id_site2id_page WHERE site_id = '{$_SESSION['site_id']}'" ) or die ( mysqli_error ( $GLOBALS['mysqli'] ) );
$array = mysqli_fetch_array ( $sql );
$fav = $array[0];

if ($fav == 0) {
	echo "$('#get_favorite_star').addClass('grey').removeClass('yellow');";
	echo "$('#get_favorite_title').popup({content:'Seite ist nicht favorisiert'});";
} else {
	echo "$('#get_favorite_star').addClass('yellow').removeClass('grey');";
	echo "$('#get_favorite_title').popup({content:'Seite favorisiert'});";
}