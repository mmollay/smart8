<?php
session_start ();
include ('../config.php');
$time = $GLOBALS['mysqli']->real_escape_string ( $_POST['time'] );

//Nur speichern wenn User_id und $time vorhanden ist	
if ($time and $_SESSION['user_id']) {
	
	$GLOBALS['mysqli']->query ( "INSERT INTO smart_gadget_meditation SET 
		user_id  = '{$_SESSION['user_id']}',
		time = '$time'
		" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	
	// Call count meditation for User
	
	$query = $GLOBALS['mysqli']->query ( "SELECT * FROM smart_gadget_meditation WHERE user_id = '{$_SESSION['user_id']}' " ) or die ( mysqli_error ($GLOBALS['mysqli']) );
	$count = mysqli_num_rows ( $query );
	
	if ($count > 9 ) $add_count = " <i class='icon empty orange star'></i>";
	elseif ($count > 49 ) $add_count = " <i class='icon star half orange star'></i>";
	elseif ($count > 99 ) $add_count = " <i class='icon orange star'></i>";
	
	echo "<br><br><br><div class='label bottom attached ui'>Dein Meditations-Counter: $count $add_count</div>";
}
?>