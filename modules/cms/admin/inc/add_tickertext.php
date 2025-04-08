<?php
include_once ('../../../login/config_main.inc.php');


$layer_id = $_POST['layer_id'];
$value = $_POST['value'];

$GLOBALS['mysqli']->query("INSERT INTO smart_gadget_ticker SET layer_id='$layer_id',text='$value'");

echo "$('#ticker_text').val('')";