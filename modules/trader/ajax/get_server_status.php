<?php
include(__DIR__ . '/../../t_config.php');
include(__DIR__ . '/../../../../../smartform/include_form.php');

header('Content-Type: application/json');
ob_end_flush();
set_time_limit(0);

$server_id = $_POST['server_id'];
$serverIp = $_POST['url'];
$token = $_SESSION['token'][$server_id];

$mh = curl_multi_init();
curl_multi_setopt($mh, CURLMOPT_MAX_TOTAL_CONNECTIONS, 100);
curl_multi_setopt($mh, CURLMOPT_MAX_HOST_CONNECTIONS, 100);

$endpoints = ["/getMT5Status", "/getActiveStrategies", "/AutoStatus", "/getStrategies"];
$channels = [];
$responses = [];

foreach ($endpoints as $key => $endpoint) {
   $ch = curl_init($serverIp . $endpoint);
   curl_setopt_array($ch, [
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
       CURLOPT_TIMEOUT => 2,
       CURLOPT_CONNECTTIMEOUT => 1,
       CURLOPT_TCP_NODELAY => true,
       CURLOPT_TCP_FASTOPEN => 1,
       CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
       CURLOPT_NOSIGNAL => 1
   ]);
   $channels[$key] = $ch;
   curl_multi_add_handle($mh, $ch);
}

$active = null;
do {
   $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
   if (curl_multi_select($mh) != -1) {
       do {
           $mrc = curl_multi_exec($mh, $active);
       } while ($mrc == CURLM_CALL_MULTI_PERFORM);
   }
}

// Ergebnisse sammeln
foreach ($channels as $key => $ch) {
   $response = curl_multi_getcontent($ch);
   $responses[basename($endpoints[$key])] = json_decode($response, true);
   curl_multi_remove_handle($mh, $ch);
   curl_close($ch);
}
curl_multi_close($mh);

// Response verarbeiten
$isMT5Active = $responses['getMT5Status']['process']['active'] ?? 0;
$response = [
   'mt5Button' => generateMT5Button($isMT5Active, $server_id)
];

if ($isMT5Active) {
   $strategies = $responses['getStrategies']['strategies'] ?? [];
   $activeStrategy = $responses['getActiveStrategies']['activeStrategy'] ?? '';
   $pause = $responses['getActiveStrategies']['pause'] ?? 0;
   $auto_value = $responses['AutoStatus']['auto'] ?? '';
   
   $response['strategyContent'] = $auto_value ? 
       generateActiveStrategyControls($server_id, $auto_value, $activeStrategy, $pause) :
       generateStrategyForm($server_id, $_POST, $strategies);
} else {
   $response['strategyContent'] = '<div class="ui message">MT5 nicht aktiv</div>';
}

$response['killButton'] = generateKillButton($server_id, $_POST['name']);

echo json_encode($response);

function generateMT5Button($isActive, $server_id) {
   $action = $isActive ? 'stopMT5' : 'startMT5';
   $text = $isActive ? 'Stop MT5' : 'Start MT5';
   $color = $isActive ? 'red' : 'green';
   $icon = $isActive ? 'stop' : 'play';
   
   return "<button onclick=\"post_ema('$action', '', '$server_id')\" 
           class='ui $color small fluid button'>
           <i class='$icon icon'></i>$text</button>";
}

function generateActiveStrategyControls($server_id, $auto_value, $activeStrategy, $pause) {
   $pauseText = $pause == 0 ? 'Pause' : 'Resume';
   $pauseColor = $pause == 0 ? 'orange' : 'green';
   $pauseIcon = $pause == 0 ? 'pause' : 'play';
   
   return "<div class='ui form small'>
           <div class='equal stackable fields'>
               <button onclick='stopAuto($server_id, $auto_value)' 
                       class='ui red small fluid button'>
                   <i class='stop icon'></i>StopAuto $auto_value
               </button>
               <button onclick=\"post_ema('pauseStrategy', '$activeStrategy', '$server_id')\" 
                       class='ui $pauseColor small fluid button'>
                   <i class='$pauseIcon icon'></i>$pauseText
               </button>
           </div>
           </div>";
}

function generateStrategyForm($server_id, $postData, $strategies) {
   $strategyOptions = '';
   foreach ($strategies as $strategy) {
       $key = $strategy['strategyName'];
       $selected = ($key == $postData['strategy_default']) ? 'selected' : '';
       $strategyOptions .= "<option value='$key' $selected>$key</option>";
   }

   return "<form class='ui form small' id='form_start$server_id'>
           <div class='equal stackable fields'>
               <div class='three wide field'>
                   <input type='text' name='size' value='{$postData['lotsize']}' placeholder='Lots'>
               </div>
               <div class='five wide field'>
                   <select name='strategy' class='ui fluid search selection dropdown'>
                       $strategyOptions
                   </select>
               </div>
               <div class='four wide field'>
                   <select name='startAuto' class='ui fluid search selection dropdown'>
                       " . implode('', array_map(fn($i) => "<option value='$i'>startAuto$i</option>", range(1, 4))) . "
                   </select>
               </div>
               <div class='four wide field'>
                   <button type='button' onclick='startStrategy($server_id)' 
                           class='ui green fluid button'>
                       <i class='play icon'></i>Start
                   </button>
               </div>
           </div>
           </form>";
}

function generateKillButton($server_id, $server_name) {
   return "<button onclick='killServer($server_id)' 
           class='ui brown icon button' 
           data-content='Stop Server: $server_name'>
           <i class='skull crossbones icon'></i>
           </button>";
}