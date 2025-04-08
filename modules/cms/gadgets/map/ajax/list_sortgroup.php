<?php
include("../../../smart_form/include_list.php");
$array = call_list ('../list/speciesgroup.php','../mysql_map.inc.php');
echo $array['html'].$array['js'];
echo "<br>";