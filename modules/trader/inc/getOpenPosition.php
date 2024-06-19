<?
include (__DIR__ . "/../t_config.php");
include (__DIR__ . '/../../../../smartform/include_form.php');

$servers = getAllServerIps($mysqli);
foreach ($servers as $serverInfo) {
    getPositionsSummary($serverIp, $_SESSION['token']['4']);
}

echo json_encode($servers);