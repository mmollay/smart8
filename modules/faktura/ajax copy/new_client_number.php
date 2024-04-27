<?php
session_start ();
require ("../config.inc.php");

echo mysql_singleoutput ( "SELECT MAX(client_number) as client_number FROM client", "client_number" ) + 1;

?>