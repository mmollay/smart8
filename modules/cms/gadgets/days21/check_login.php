<?php
//session_start ();
if (!$_SESSION['userbar_id']) {
	
	echo "alert('Bitte einloggen um bewerten zu können') ";
	
	exit ();
}