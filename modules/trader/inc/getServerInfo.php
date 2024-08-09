<?php
include (__DIR__ . "/../t_config.php");
include (__DIR__ . '/../../../../smartform/include_form.php');

$query = "SELECT server_id, url FROM ssi_trader.servers WHERE active = 1";

$ips = []; // Initialisiere das Array vor der Verwendung

if ($stmt = $mysqli->prepare($query)) {
    // Die bind_param Zeile wurde entfernt, da sie hier nicht benötigt wird
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $arrayPositionSummnery = getPositionsSummary($row['url'], $_SESSION['token'][$row['server_id']]);
        $ips[] = [
            'server_id' => $row['server_id'],
            'url' => $row['url'],
            'numberOfPositions' => $arrayPositionSummnery['numberOfPositions'],
            'totalLots' => $arrayPositionSummnery['totalLots'],
            'sumOfValuesAtPositionPrice15' => $arrayPositionSummnery['sumOfValuesAtPositionPrice15'],
            'daxValue' => $arrayPositionSummnery['daxValue'],
            'margin' => $arrayPositionSummnery['margin']
        ];
    }
    $stmt->close(); // Verschiebe das Schließen des Statements außerhalb der Schleife
    echo json_encode($ips);
}
