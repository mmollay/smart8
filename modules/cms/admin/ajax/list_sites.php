<?php
require ('../../config.inc.php');
include("../../smart_form/include_list.php");
$array = call_list ('../list/sites.php','../../../login/config_main.inc.php');
echo $array['html'].$array['js'];
