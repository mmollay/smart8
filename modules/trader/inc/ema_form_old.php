<?php
include(__DIR__ . '/../t_config.php');
include(__DIR__ . '/../../../../smartform/include_form.php');

$serverIps = getAllServerIps($mysqli);

// Multi-cURL Setup
$mh = curl_multi_init();
curl_multi_setopt($mh, CURLMOPT_MAX_TOTAL_CONNECTIONS, 100);
curl_multi_setopt($mh, CURLMOPT_MAX_HOST_CONNECTIONS, 100);

$channels = [];
$serverData = [];

// Requests für jeden Server vorbereiten
foreach ($serverIps as $serverInfo) {
    $server_id = $serverInfo['server_id'];
    $serverIp = $serverInfo['url'];
    $token = $_SESSION['token'][$server_id];

    // MT5 Status Request
    $mt5Ch = curl_init($serverIp . "/getMT5Status");
    curl_setopt_array($mt5Ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
        CURLOPT_TIMEOUT => 2,
        CURLOPT_TCP_NODELAY => true
    ]);
    $channels[$server_id . '_mt5'] = $mt5Ch;
    curl_multi_add_handle($mh, $mt5Ch);

    // Status Request
    $statusCh = curl_init($serverIp . "/getStatus");
    curl_setopt_array($statusCh, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
        CURLOPT_TIMEOUT => 2,
        CURLOPT_TCP_NODELAY => true
    ]);
    $channels[$server_id . '_status'] = $statusCh;
    curl_multi_add_handle($mh, $statusCh);

    if ($isMT5Active) {
        // Strategies Request
        $strategiesCh = curl_init($serverIp . "/getStrategies");
        curl_setopt_array($strategiesCh, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
            CURLOPT_TIMEOUT => 2,
            CURLOPT_TCP_NODELAY => true
        ]);
        $channels[$server_id . '_strategies'] = $strategiesCh;
        curl_multi_add_handle($mh, $strategiesCh);
    }

    $serverData[$server_id] = $serverInfo;
}

// Parallel ausführen
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
$responses = [];
foreach ($channels as $key => $ch) {
    list($server_id, $type) = explode('_', $key);
    $response = curl_multi_getcontent($ch);
    $responses[$server_id][$type] = json_decode($response, true);
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);

// HTML Generierung
$tr = ['real' => '', 'demo' => ''];

foreach ($serverData as $server_id => $serverInfo) {
    $mt5Data = $responses[$server_id]['mt5'] ?? [];
    $statusData = $responses[$server_id]['status'] ?? [];
    $strategiesData = $responses[$server_id]['strategies'] ?? [];

    $isMT5Active = $mt5Data['process']['active'] ?? 0;

    // Button Generierung
    $buttonAction = $isMT5Active ? 'stopMT5' : 'startMT5';
    $buttonValue = $isMT5Active ? 'Stop MT5' : 'Start MT5';
    $colorClass = $isMT5Active ? "red" : "green";
    $icon = $isMT5Active ? "stop" : "play";

    $arr['form'] = array();
    $arr['field'][] = ['type' => 'button', 'value' => $buttonValue, 'onclick' => "post_ema('$buttonAction','','$server_id')", 'class' => "{$colorClass} small fluid", 'icon' => $icon];
    $output = call_form($arr);
    $ContentButtonMT5 = $output['html'];

    if ($isMT5Active) {
        $activeStrategy = $statusData['activeStrategy'] ?? '';
        $statusAuto = $statusData['auto'] ?? '';
        $pauseStrategy = $statusData['pauseStrategy'] ?? 0;
        $pauseAuto = $statusData['pauseAuto'] ?? 0;
        $sizeStrategy = $statusData['sizeStrategy'] ?? '';
        $sizeAuto = $statusData['sizeAuto'] ?? '';

        if ($statusAuto) {
            // Auto Controls
            $buttonText = $pauseAuto == 0 ? 'Pause Auto' : 'Resume';
            $buttonColor = $pauseAuto == 0 ? 'orange' : 'green';
            $buttonIcon = $pauseAuto == 0 ? 'pause' : 'play';

            $arr['form'] = array('class' => 'small');
            $arr['field'][] = array('type' => 'div', 'class' => 'equal stackable fields');
            $arr['field'][] = ['type' => 'button', 'value' => "StopAuto $statusAuto", 'onclick' => "stopAuto($server_id,$statusAuto)", 'class' => "red small fluid", 'icon' => 'stop'];
            $arr['field'][] = ['type' => 'button', 'value' => $buttonText, 'onclick' => "post_ema_general('pauseAuto','$server_id')", 'class' => "$buttonColor small fluid", 'icon' => $buttonIcon];
            $arr['field'][] = array('type' => 'div_close');
            $output = call_form($arr);
            $ContentStopPause = $output['html'];
            $lotsize = $sizeAuto;
        } else if ($activeStrategy) {
            // Strategy Controls
            $buttonText = $pauseStrategy == 0 ? 'Pause Strategy' : 'Resume';
            $buttonColor = $pauseStrategy == 0 ? 'orange' : 'green';
            $buttonIcon = $pauseStrategy == 0 ? 'pause' : 'play';

            $arr['form'] = array('class' => 'small');
            $arr['field'][] = array('type' => 'div', 'class' => 'equal stackable fields');
            $arr['field'][] = ['type' => 'button', 'value' => "Stop Strategy", 'onclick' => "post_ema('stopStrategy','','$server_id')", 'class' => "red small fluid", 'icon' => 'stop'];
            $arr['field'][] = ['type' => 'button', 'value' => $buttonText, 'onclick' => "post_ema('pauseStrategy','','$server_id')", 'class' => "$buttonColor small fluid", 'icon' => $buttonIcon];
            $arr['field'][] = array('type' => 'div_close');
            $output = call_form($arr);
            $ContentStopPause = $output['html'];
            $lotsize = $sizeStrategy;
        } else {
            // Strategy Form
            $arrayStrategies = [];
            foreach ($strategiesData['strategies'] as $strategy) {
                $arrayStrategies[$strategy['strategyName']] = $strategy['strategyName'];
            }

            $arr['form'] = array('action' => "ajax/post.php", 'id' => "form_start$server_id", 'class' => 'small');
            $arr['ajax'] = array('success' => "after_start_strategy(data)", 'dataType' => "html");
            $arr['field'][] = array('type' => 'div', 'class' => 'equal stackable fields');
            $arr['field']['size'] = array('type' => 'input', 'value' => $serverInfo['lotsize'], 'validate' => true, 'wide' => 'three', 'placholder' => 'Lots');

            $arrayAuto = array('startAuto1' => 'startAuto1', 'startAuto2' => 'startAuto2', 'startAuto3' => 'startAuto3', 'startAuto4' => 'startAuto4');
            $mergedArray = array_merge($arrayAuto, $arrayStrategies);

            $arr['field']['strategy'] = array('type' => 'dropdown', 'array' => $mergedArray, 'class' => 'fluid search selection', 'validate' => true, 'value' => $serverInfo['strategy_default'], 'placeholder' => 'Strategy', 'wide' => 'five');
            $arr['field'][] = ['type' => 'submit', 'value' => "Start", 'class' => "fluid green", 'icon' => 'play', 'wide' => 'four'];
            $arr['field'][] = array('type' => 'div_close');
            $arr['hidden']['server_id'] = $server_id;
            $arr['hidden']['account'] = $serverInfo['account'];
            $arr['hidden']['strategy_value'] = 'startStrategy';

            $output = call_form($arr);
            $ContentStartStrategy = $output['html'] . $output['js'];
        }

        // Kill Button
        $arr['form'] = array('action' => "ajax/post.php", 'id' => 'kill_all', 'class' => 'small');
        $arr['ajax'] = array('success' => "after_post_request(data)", 'dataType' => "html", "confirmation" => true);
        $arr['ajax']['confirmation'] = array('text' => array('content' => 'Are you sure to Stop the Server: ' . $serverInfo['name'] . '? '));
        $arr['hidden']['kill_all'] = '1';
        $arr['hidden']['server_id'] = $server_id;
        $arr['field']['button'] = array('type' => 'submit', 'value' => "", 'icon' => 'skull crossbones', 'class' => 'brown');
        $output = call_form($arr);
        $contenKillAll = "<div class='four wide column'>" . $output['html'] . $output['js'] . "</div>";
    }

    // Tabellenzeile generieren
    $serverType = $serverInfo['real_account'] == 1 ? 'real' : 'demo';

    $tr[$serverType] .= "<tr>";
    $tr[$serverType] .= "<td>
       <b>{$serverInfo['name']}</b><br>
       {$serverInfo['url']}<br>
       Account: {$serverInfo['account']} : {$serverInfo['broker_matchcode']}
   </td>";
    $tr[$serverType] .= "<td>$ContentButtonMT5</td>";

    if ($activeStrategy) {
        $tr[$serverType] .= "<td>$lotsize</td>";
        $tr[$serverType] .= "<td>$activeStrategy</td>";
        $colspan = 0;
    } else {
        $colspan = 3;
    }

    $tr[$serverType] .= "<td colspan=$colspan>$ContentStartStrategy $ContentStopPause $ContentClose</td>";
    $tr[$serverType] .= "<td style='text-align: right;'><span id='server-positions-$server_id'>..loading</span></td>";
    $tr[$serverType] .= "<td style='text-align: right;' class=''></td>";
    $tr[$serverType] .= "<td><div align='center'>$contenKillAll</div></td>";
    $tr[$serverType] .= "</tr>";

    // Reset variables
    $ContentButtonMT5 = '';
    $ContentStopPause = '';
    $ContentClose = '';
    $ContentStartStrategy = '';
    $contenKillAll = '';
}

// Tabellen ausgeben
function printServerTable($title, $tbodyContent, $serverType)
{
    if (!$tbodyContent)
        return;

    $labelStrategy = $serverType == 'real' ? "<div class='ui label red'>Real</div>" : "<div class='ui label orange'>Demo</div>";
    echo "<h3>{$labelStrategy} {$title}</h3>";
    echo "<table class='ui small compact celled striped single line table' style='max-width:1200px'>";
    echo "<thead><tr>
       <th class='wide four'>Server Details</th>
       <th>MT5 Server</th>
       <th>Lots</th>
       <th colspan='2'>Strategy</th>
       <th>Info</th>
       <th class=''></th>
       <th>Kill</th>
   </tr></thead>";
    echo "<tbody>{$tbodyContent}</tbody>";
    echo "</table>";
}

printServerTable("Servers", $tr['real'], 'real');
printServerTable("Servers", $tr['demo'], 'demo');
?>