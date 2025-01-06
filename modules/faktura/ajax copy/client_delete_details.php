<?php
require ("../config.inc.php");

$GLOBALS['mysqli']->query ( "DELETE from sections WHERE client_id = '{$_POST['id']}'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );
$GLOBALS['mysqli']->query ( "DELETE from membership WHERE client_id = '{$_POST['id']}'" ) or die ( mysqli_error ($GLOBALS['mysqli']) );

?>