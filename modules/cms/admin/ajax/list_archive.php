<?php
include("../../smart_form/include_list.php");

//Defaultmässig wird die gewählte Seite genommen
$_SESSION["filter"]['archive_list']['site_id'] = $_SESSION['site_id'];

$array = call_list ('../list/archive.php','../../../login/config_main.inc.php');
echo $array['html'].$array['js'];