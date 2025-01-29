<?php
include(__DIR__ . "/../t_config.php");
include(__DIR__ . '/../../../smartform/include_form.php');

$field_killall = '';
$real_account = 0;

//Strategies for dropdown
$arrayStrategies = getStrategyNames($ServerPrime, $_SESSION['token']['4']);

$serverIps = getAllServerIps($mysqli);

// Initialisiere das Array, das die Tabellenreihen aufnehmen soll
$tr = [
    'real' => '',
    'demo' => ''
];

foreach ($serverIps as $serverInfo) {
    $serverIp = $serverInfo['url'];
    $server_id = $serverInfo['server_id'];
    $server_name = $serverInfo['name'];
    $brokerName = $serverInfo['broker_name'];
    $strategy_default = $serverInfo['strategy_default'];
    $daily_profit = $serverInfo['daily_profit'];
    $previous_day_profit = $serverInfo['previous_day_profit'];
    $lotsize_default = $serverInfo['lotsize'];
    $real_account = $serverInfo['real_account'];
    $token = $_SESSION['token'][$server_id]; //Token wird in "generate_token.php" gespeichert
    $numberOfPositions = $serverInfo['numberOfPositions'];
    $sumOfValuesAtPosition9 = $serverInfo['sumOfValuesAtPosition9'];
    $account = $serverInfo['account'];
    $broker_matchcode = $serverInfo['broker_matchcode'];

    // Entscheide, ob der Server real oder Demo ist
    $serverType = $serverInfo['real_account'] == 1 ? 'real' : 'demo';

    $tr[$serverType] .= "<tr><td><b>" . $serverInfo['name'] . "</b><br>$serverIp<br>Account: $account : $broker_matchcode</td>";
    $tr[$serverType] .= "<td>MT5-Button</td>";

    if ($activeStrategy) {
        $tr[$serverType] .= "<td>$lotsize</td>";
        $tr[$serverType] .= "<td>$activeStrategy</td>";
        $colspan = 0;
    } else {
        $colspan = 3;
    }

    $tr[$serverType] .= "<td colspan=$colspan>$ContentStartStrategy $ContentStopPause $ContentClose</td>";
    $tr[$serverType] .= "<td style='text-align: right;'><span id='server-positions-$server_id'>..loading</span></td>"; // Rechtbündig
    //$tr[$serverType] .= "<td style='text-align: right;'><span id='server-profit-$server_id'>" . $daily_profit . "</span></td>"; // Rechtbündig
    $tr[$serverType] .= "<td style='text-align: right;' class=''></td>"; // Rechtbündig
    $tr[$serverType] .= "<td><div align='center'>$contenKillAll</div></td>";
    $tr[$serverType] .= "</tr>";

    $contenKillAllBulk .= $contenKillAll;
    $ContentButtonMT5 = '';
    $ContentStopPause = '';
    $ContentClose = '';
    $ContentStartStrategy = '';
    $contenKillAll = '';

}


function printServerTable($title, $tbodyContent, $serverType)
{
    // Bestimmt das Label basierend auf dem übergebenen Server-Typ
    $labelStrategy = $serverType == 'real' ? "<div class='ui label red'>Real</div>" : "<div class='ui label orange'>Demo</div>";
    if ($tbodyContent) {
        echo "<h3>{$labelStrategy} {$title} </h3>";
        echo "<table class='ui small compact celled striped single line table ' style='max-width:1100px'>";
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
}

// Ausgabe für Real Server
printServerTable("Servers", $tr['real'], 'real');

// Ausgabe für Demo Server
printServerTable("Servers", $tr['demo'], 'demo');
