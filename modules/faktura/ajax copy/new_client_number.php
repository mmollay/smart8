<?php
session_start();
include (__DIR__ . '/../f_config.php');

echo mysql_singleoutput("SELECT MAX(client_number) as client_number FROM client", "client_number") + 1;

?>