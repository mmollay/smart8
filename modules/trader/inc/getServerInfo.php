<?php
include(__DIR__ . "/../t_config.php");
include(__DIR__ . '/../../../../smartform/include_form.php');

$query = "SELECT server_id, url FROM ssi_trader.servers WHERE active = 1";

if ($stmt = $mysqli->prepare($query)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $servers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Multi-cURL Setup
    $mh = curl_multi_init();
    curl_multi_setopt($mh, CURLMOPT_MAX_TOTAL_CONNECTIONS, 100);
    curl_multi_setopt($mh, CURLMOPT_MAX_HOST_CONNECTIONS, 100);

    $channels = [];
    foreach ($servers as $server) {
        $ch = curl_init($server['url'] . "/openPositions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $_SESSION['token'][$server['server_id']]],
            CURLOPT_TIMEOUT => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TCP_NODELAY => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_NOSIGNAL => 1
        ]);
        $channels[$server['server_id']] = $ch;
        curl_multi_add_handle($mh, $ch);
    }

    // Ausführen aller Requests parallel
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
    $ips = [];
    foreach ($channels as $server_id => $ch) {
        $response = curl_multi_getcontent($ch);
        $data = json_decode($response, true);

        $positions = $data['position'] ?? [];
        $totalLots = 0;
        $sumPrice15 = 0;

        foreach ($positions as $position) {
            $lotSize = $position[9] ?? 0;
            $totalLots += abs($lotSize);
            $sumPrice15 += $position[15] ?? 0;
        }

        $ips[] = [
            'server_id' => $server_id,
            'url' => $servers[array_search($server_id, array_column($servers, 'server_id'))]['url'],
            'numberOfPositions' => count($positions),
            'totalLots' => $totalLots,
            'sumOfValuesAtPositionPrice15' => $sumPrice15,
            'margin' => abs($totalLots * ($position[10] ?? 0) / 100) * 1.09
        ];

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    echo json_encode($ips);
}