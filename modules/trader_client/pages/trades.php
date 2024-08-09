<?php
include (__DIR__ . "/../check_permission.php");
include (__DIR__ . "/../smartform/include_list.php");
$array = call_list('../list/trades_array.php', '../config.php');
echo $array['html'] . $array['js'];
