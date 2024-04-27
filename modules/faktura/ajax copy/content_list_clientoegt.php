<?
// Parameter für Table
// include("../config.inc.php");
include ('../../ssi_smart/smart_form/include_list.php');

$array = call_list ( '../list/client_oegt.php', '../config.inc.php' );
echo $array ['html'] . $array ['js'] . "<script type=\"text/javascript\" src=\"js/list_clientoegt.js\"></script>";