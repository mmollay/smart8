<?php
include (__DIR__ . '/../t_config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['createStrategy'])) {
        $array = fetchAllServers($mysqli);
        foreach ($array as $i => $server) {
            if ($_POST['server' . $server['server_id']] == 1) {
                $data = fetchDataForGroup($mysqli, $_POST['createStrategy']);
                $url = $server['url'] . '/createStrategy';
                $jsonString = sendCurlRequest($url, $data, $_SESSION['token'][$server['server_id']]);
                $response = json_decode($jsonString, true);
            }
        }
        echo $jsonString;
        exit;
    }

    // Konvertiere 'qty' zu einem Float, falls es gesetzt ist
    if (isset($_POST['qty'])) {
        $_POST['qty'] = floatval($_POST['qty']);
    }

    if (isset($_POST['price'])) {
        $_POST['price'] = floatval($_POST['price']);
    }

    // Setze 'qty' auf 0.1, falls der Wert kleiner als 0 ist
    if ($_POST['qty'] < 0) {
        $_POST['qty'] = 0.1;
    }

    $server_id = $_POST['server_id'];
    $token = $_SESSION['token'][$server_id];
    $result = getServerIdGetUrl($mysqli, $server_id);
    if ($result["success"]) {
        // Erfolg - verarbeite die gefundene URL
        $url = $result["url"];
    } else {
        $url = getSingleServerIpByUserId($mysqli, $_SESSION['user_id']);
    }

    // Überprüfung, welche Aktion ausgeführt werden soll
    switch (true) {
        case ($_POST['strategy_value'] == 'startMT5'):
            $data = getMT5BrokerData($mysqli, $server_id);
            $url .= '/startMT5';
            break;
        case ($_POST['strategy_value'] == 'stopMT5'):
            $url .= '/stopMT5';
            break;
        //start Strategy
        case ($_POST['strategy_value'] == 'startStrategy'):
            //$url .= '/startStrategy';
            $url .= '/startAuto' . $_POST['startAuto'];
            $account = $_POST['account']; // Empfange den Account aus dem Post-Request    
            $size = floatval($_POST['size']);
            $data = array('strategy' => $_POST['strategy'], 'size' => $size, 'contract' => $_POST['contract']);
            // Rufe die neue Funktion auf mit dem Account
            $lastId = assignStrategyToServer($_POST['strategy'], $size, $_POST['server_id'], $_POST['account'], $mysqli);
            break;
        //stop Auto
        case ($_POST['stopAuto']):
            $url .= '/stopAuto' . $_POST['auto_value'];
            break;
        //pauserAuto
        case ($_POST['pauseAuto']):
            $url .= '/pauseAuto' . $_POST['auto_value'];
            break;

        //stop Strategy
        case ($_POST['strategy_value'] == 'stopStrategy'):
            $url .= '/stopStrategy';
            //$url .= "/stop" . $_POST['strategy'];
            //$data = array('strategy' => $_POST['strategy']);
            //echo $url;
            //print_r($data);
            break;
        //pause Strategy
        case ($_POST['strategy_value'] == 'pauseStrategy'):
            $url .= '/pauseStrategy';
            //$data = array('strategy' => $_POST['strategy']);
            break;
        //close Strategy
        case ($_POST['strategy_value'] == 'closeAll'):
            $url .= '/closeAll';
            $data = array('strategy' => $_POST['strategy']);
            echo $url;
            break;
        //killall
        case ($_POST['kill_all'] == 1):
            $url .= '/panic';
            break;
        case ($_POST['buy_sell'] == 'sell'):
            $url .= '/sellMarket';
            $data = array('qty' => $_POST['qty']);
            break;
        case ($_POST['buy_sell'] == 'buy'):
            $url .= '/buyMarket';
            $data = array('qty' => $_POST['qty']);
            break;
        case ($_POST['buy_sell_stop'] == 'buyStop'):
            $url .= '/buyStop';
            $data = array('qty' => $_POST['qty'], 'price' => $_POST['price']);
            break;
        case ($_POST['buy_sell_stop'] == 'sellStop'):
            $url .= '/sellStop';
            $data = array('qty' => $_POST['qty'], 'price' => $_POST['price']);
            break;
        case ($_POST['buy_sell_limit'] == 'buyLimit'):
            $url .= '/buyLimit';
            $data = array('qty' => $_POST['qty'], 'price' => $_POST['price']);
            break;
        case ($_POST['buy_sell_limit'] == 'sellLimit'):
            $url .= '/sellLimit';
            $data = array('qty' => $_POST['qty'], 'price' => $_POST['price']);
            break;
    }

    $jsonString = sendCurlRequest($url, $data, $token);
    $response = json_decode($jsonString, true);

    //Wenn error als response zurückkommt, dann lösche den zuletzt eingetragenen Datensatz
    if (isset($response['error'])) {
        // Ein Fehler ist aufgetreten, lösche den zuletzt eingetragenen Datensatz
        deleteLastStrategyAssignment($lastId, $mysqli);
    }

    echo $jsonString;
}

function assignStrategyToServer($strategy, $size, $serverId, $account, $mysqli)
{
    $stmt = $mysqli->prepare("INSERT INTO ssi_trader.strategy_assignments (strategy, size, server_id, account) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $strategy, $size, $serverId, $account);
    if ($stmt->execute()) {
        // Hole die ID des zuletzt eingefügten Datensatzes
        $lastId = $mysqli->insert_id;
        // echo "Strategie und Account erfolgreich zugewiesen.";
        $stmt->close();
        return $lastId; // Gib die ID zurück
    } else {
        //echo "Fehler bei der Zuweisung der Strategie und des Accounts.";
        $stmt->close();
        return false;
    }
}

function deleteLastStrategyAssignment($id, $mysqli)
{
    if ($id) {
        $stmt = $mysqli->prepare("DELETE FROM ssi_trader.strategy_assignments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        // echo "Der letzte Datensatz wurde aufgrund eines Fehlers gelöscht.";
    }
}


//erzeugt ein array für das erzeugen der Strategie auf den Win-Server
function fetchDataForGroup($mysqli, $groupId)
{
    // Zuerst den Titel der Strategie auslesen
    $stmt = $mysqli->prepare("SELECT title FROM ssi_trader.hedging_group WHERE group_id = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    $strategyTitle = $result->fetch_assoc()['title'];
    $stmt->close();

    // Daten aus der 'hedging' Tabelle auslesen
    $stmt = $mysqli->prepare("SELECT Size, EntryPrice, TP, Switch, Side FROM ssi_trader.hedging WHERE group_id = ? ORDER BY level");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();

    $iterations = [];
    while ($row = $result->fetch_assoc()) {
        $iterations[] = [
            'Size' => (float) $row['Size'],
            'EntryPrice' => (float) $row['EntryPrice'],
            'TP' => (float) $row['TP'],
            'Switch' => (bool) $row['Switch'],
            'Side' => (int) $row['Side']
        ];
    }
    $stmt->close();

    // Kombiniere die Daten in einem Array
    $data = [
        'strategy' => $strategyTitle,
        'iterations' => $iterations
    ];

    return $data;
}
