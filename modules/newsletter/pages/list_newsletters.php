<?
//Parameter für Table
include (__DIR__ . '/../../../../smartform/include_list.php');

$array = call_list('../list/newsletters.php', '../n_config.php');
echo $array['html'] . $array['js'];