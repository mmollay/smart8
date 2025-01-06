<?php
include ("../../ssi_smart/smart_form/include_list.php");

$array = call_list ( '../list/accountgroups.php', '../config.inc.php',array('option'=>'in') );
echo $array['html'].$array['js'];