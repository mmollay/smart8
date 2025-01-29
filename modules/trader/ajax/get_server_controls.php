<?php
include(__DIR__ . '/../t_config.php');
include(__DIR__ . '/../../../../smartform/include_form.php');

header('Content-Type: application/json');

$query = "SELECT s.*, b.user account, b.title broker_matchcode, b.lotsize, b.strategy_default 
          FROM ssi_trader.servers s 
          LEFT JOIN ssi_trader.broker b ON s.broker_id = b.broker_id 
          WHERE s.active = 1";


$servers = [];

if ($stmt = $mysqli->prepare($query)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $serverRows = $result->fetch_all(MYSQLI_ASSOC);

    $mh = curl_multi_init();
    curl_multi_setopt($mh, CURLMOPT_MAX_TOTAL_CONNECTIONS, 100);
    curl_multi_setopt($mh, CURLMOPT_MAX_HOST_CONNECTIONS, 100);

    $channels = [];

    foreach ($serverRows as $server) {
        $server_id = $server['server_id'];
        $serverIp = $server['url'];
        $token = $_SESSION['token'][$server_id];

        $endpoints = [
            'mt5' => "/getMT5Status",
            'status' => "/getStatus",
            'strategies' => "/getStrategies"
        ];

        foreach ($endpoints as $type => $endpoint) {
            $ch = curl_init($serverIp . $endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
                CURLOPT_TIMEOUT => 2,
                CURLOPT_TCP_NODELAY => true,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_ENCODING => '',
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]);
            $channels[$server_id . '_' . $type] = $ch;
            curl_multi_add_handle($mh, $ch);
        }
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

    $responses = [];
    foreach ($channels as $key => $ch) {
        list($server_id, $type) = explode('_', $key);
        $response = curl_multi_getcontent($ch);
        $responses[$server_id][$type] = json_decode($response, true);
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);

    foreach ($serverRows as $server) {
        $server_id = $server['server_id'];
        $serverResponse = [
            'server_id' => $server_id,
            'mt5Button' => '',
            'strategyLot' => '',
            'controls' => '',
            'killButton' => ''
        ];

        $mt5Data = $responses[$server_id]['mt5'] ?? [];
        $statusData = $responses[$server_id]['status'] ?? [];
        $strategiesData = $responses[$server_id]['strategies'] ?? [];

        $isMT5Active = $mt5Data['process']['active'] ?? 0;
        $serverResponse['mt5Button'] = generateMT5Button($isMT5Active, $server_id);

        if ($isMT5Active) {
            $auto = $statusData['auto'] ?? '';
            $activeStrategy = $statusData['activeStrategy'] ?? '';

            if ($auto) {
                $pauseAuto = $statusData['pauseAuto'] ?? 0;
                $sizeAuto = $statusData['sizeAuto'] ?? '';
                $serverResponse['strategyLot'] = "$sizeAuto Lot - Auto $auto";
                $serverResponse['controls'] = generateAutoControls($server_id, $auto, $pauseAuto);
            } elseif ($activeStrategy) {
                $pauseStrategy = $statusData['pauseStrategy'] ?? 0;
                $sizeStrategy = $statusData['sizeStrategy'] ?? '';
                $serverResponse['strategyLot'] = "$sizeStrategy Lot - $activeStrategy";
                $serverResponse['controls'] = generateStrategyControls($server_id, $pauseStrategy);
            } else {
                $arrayStrategies = [];
                if (!empty($strategiesData['strategies'])) {
                    foreach ($strategiesData['strategies'] as $strategy) {
                        $arrayStrategies[$strategy['strategyName']] = $strategy['strategyName'];
                    }
                }
                $serverResponse['strategyLot'] = generateStartForm($server_id, $server, $arrayStrategies);
            }

            $serverResponse['killButton'] = generateKillButton($server_id, $server['name']);
        }

        $servers[] = $serverResponse;
    }

    echo json_encode($servers);
}

function generateMT5Button($isActive, $server_id)
{
    $action = $isActive ? 'stopMT5' : 'startMT5';
    $text = $isActive ? 'Stop MT5' : 'Start MT5';
    $color = $isActive ? "red" : "green";
    $icon = $isActive ? "stop" : "play";

    return "<button onclick=\"post_ema('$action', '', '$server_id')\" 
           class='ui $color small fluid button'>
           <i class='$icon icon'></i>$text</button>";
}

function generateAutoControls($server_id, $auto, $pauseAuto)
{
    $buttonText = $pauseAuto == 0 ? 'Pause Auto' : 'Resume';
    $buttonColor = $pauseAuto == 0 ? 'orange' : 'green';
    $buttonIcon = $pauseAuto == 0 ? 'pause' : 'play';

    return "<div class='ui form small'>
           <div class='equal stackable fields'>
               <button onclick='stopAuto($server_id, $auto)' 
                       class='ui red small fluid button'>
                   <i class='stop icon'></i>StopAuto $auto
               </button>
               <button onclick=\"post_ema_general('pauseAuto','$server_id')\" 
                       class='ui $buttonColor small fluid button'>
                   <i class='$buttonIcon icon'></i>$buttonText
               </button>
           </div>
           </div>";
}

function generateStrategyControls($server_id, $pauseStrategy)
{
    $buttonText = $pauseStrategy == 0 ? 'Pause Strategy' : 'Resume';
    $buttonColor = $pauseStrategy == 0 ? 'orange' : 'green';
    $buttonIcon = $pauseStrategy == 0 ? 'pause' : 'play';

    return "<div class='ui form small'>
           <div class='equal stackable fields'>
               <button onclick=\"post_ema('stopStrategy','','$server_id')\" 
                       class='ui red small fluid button'>
                   <i class='stop icon'></i>Stop Strategy
               </button>
               <button onclick=\"post_ema('pauseStrategy','','$server_id')\" 
                       class='ui $buttonColor small fluid button'>
                   <i class='$buttonIcon icon'></i>$buttonText
               </button>
           </div>
           </div>";
}

function generateStartForm($server_id, $server, $strategies = [])
{
    $arrayAuto = [
        'startAuto1' => 'startAuto1',
        'startAuto2' => 'startAuto2',
        'startAuto3' => 'startAuto3',
        'startAuto4' => 'startAuto4'
    ];

    $mergedArray = array_merge($arrayAuto, $strategies);

    return "<form class='ui form small' id='form_start$server_id'>
           <div class='equal stackable fields'>
               <div class='three wide field'>
                   <input type='text' name='size' 
                          value='{$server['lotsize']}' 
                          placeholder='Lots'>
               </div>
               <div class='five wide field'>
                   <select name='strategy' class='ui fluid search selection dropdown'>
                       " . generateOptions($mergedArray, $server['strategy_default']) . "
                   </select>
               </div>
               <div class='four wide field'>
                   <button type='button' 
                           onclick='startStrategy($server_id)' 
                           class='ui green fluid button'>
                       <i class='play icon'></i>Start
                   </button>
               </div>
           </div>
           <input type='hidden' name='server_id' value='$server_id'>
           <input type='hidden' name='account' value='{$server['account']}'>
           <input type='hidden' name='strategy_value' value='startStrategy'>
           </form>";
}

function generateOptions($array, $selected)
{
    $options = '';
    foreach ($array as $key => $value) {
        $selectedAttr = ($key == $selected) ? 'selected' : '';
        $options .= "<option value='$key' $selectedAttr>$value</option>";
    }
    return $options;
}

function generateKillButton($server_id, $server_name)
{
    return "<button onclick='killServer($server_id)' 
           class='ui brown icon button' 
           data-content='Stop Server: $server_name'>
           <i class='skull crossbones icon'></i>
           </button>";
}
?>