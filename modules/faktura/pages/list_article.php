<?php
include (__DIR__ . '/../../../../smartform/include_list.php');

$array = call_list('../list/article.php', '../f_config.php');
echo $array['html'] . $array['js'];