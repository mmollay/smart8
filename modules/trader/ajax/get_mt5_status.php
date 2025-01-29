<?php
include(__DIR__ . '/../t_config.php');
include(__DIR__ . '/../../../../smartform/include_form.php');

$server_id = $_POST['server_id'];
$serverIp = $_POST['url'];
$token = $_SESSION['token'][$server_id];

$json_string = sendCurlRequest($serverIp . "/getMT5Status", '', $token);
$array = json_decode($json_string, true);
$isMT5Active = $array['process']['active'] == 1;

$buttonAction = $isMT5Active ? 'stopMT5' : 'startMT5';
$buttonValue = $isMT5Active ? 'Stop MT5' : 'Start MT5';
$colorClass = $isMT5Active ? "red" : "green";
$icon = $isMT5Active ? "stop" : "play";

echo "<button onclick=\"post_ema('$buttonAction','','$server_id')\" 
      class=\"ui $colorClass small fluid button\">
      <i class=\"$icon icon\"></i>$buttonValue</button>";