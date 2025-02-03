<?php
require_once('../config/config.php');

try {
    // Validiere Request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST-Requests erlaubt');
    }

    // Hole POST-Daten
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Ungültige JSON-Daten');
    }

    // Validiere erforderliche Felder
    $requiredFields = ['symbol', 'position_type', 'position_size', 'leverage'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Feld '$field' ist erforderlich");
        }
    }

    // Initialisiere Klassen
    $api = new BitgetAPI();
    $analyzer = new MarketAnalyzer();
    $db = DatabaseManager::getInstance();

    // Analysiere Markt
    $analysis = $analyzer->analyze($data['symbol']);
    
    // Prüfe Handelssignale
    if ($analysis['recommendation']['type'] !== $data['position_type']) {
        throw new Exception('Handelssignal nicht bestätigt');
    }

    if ($analysis['recommendation']['confidence'] < 60) {
        throw new Exception('Zu geringe Signalstärke');
    }

    // Prüfe offene Positionen
    $positions = $api->getPositions($data['symbol']);
    if (!empty($positions['data'])) {
        throw new Exception('Es gibt bereits offene Positionen');
    }

    // Setze Hebel
    $api->setLeverage($data['symbol'], $data['leverage']);

    // Berechne Stop-Loss und Take-Profit
    $currentPrice = $analysis['currentPrice'];
    $atr = $analysis['indicators']['atr'];
    
    if ($data['position_type'] === 'long') {
        $stopLoss = $currentPrice - ($atr * SL_ATR_MULTIPLIER);
        $takeProfit = $currentPrice + ($atr * SL_ATR_MULTIPLIER * MIN_RR_RATIO);
    } else {
        $stopLoss = $currentPrice + ($atr * SL_ATR_MULTIPLIER);
        $takeProfit = $currentPrice - ($atr * SL_ATR_MULTIPLIER * MIN_RR_RATIO);
    }

    // Platziere Hauptorder
    $side = $data['position_type'] === 'long' ? 'open_long' : 'open_short';
    $order = $api->placeMarketOrder(
        $data['symbol'],
        $side,
        $data['position_size'],
        $data['leverage']
    );

    if (!isset($order['data']['orderId'])) {
        throw new Exception('Order konnte nicht platziert werden');
    }

    // Speichere Trade in Datenbank
    $tradeData = [
        'symbol' => $data['symbol'],
        'position_type' => $data['position_type'],
        'entry_price' => $currentPrice,
        'position_size' => $data['position_size'],
        'leverage' => $data['leverage'],
        'take_profit' => $takeProfit,
        'stop_loss' => $stopLoss,
        'order_id' => $order['data']['orderId']
    ];

    $tradeId = $db->saveTrade($tradeData);

    // Speichere Signale
    $db->saveTradeSignals($tradeId, $analysis['signals']);

    // Speichere Indikatoren
    $db->saveIndicators(array_merge(
        $analysis['indicators'],
        ['symbol' => $data['symbol']]
    ));

    // Platziere Stop-Loss
    $slSide = $data['position_type'] === 'long' ? 'close_long' : 'close_short';
    $api->placeStopLoss(
        $data['symbol'],
        $slSide,
        $data['position_size'],
        $stopLoss,
        $data['leverage']
    );

    // Platziere Take-Profit
    $tpSide = $data['position_type'] === 'long' ? 'close_long' : 'close_short';
    $api->placeTakeProfit(
        $data['symbol'],
        $tpSide,
        $data['position_size'],
        $takeProfit,
        $data['leverage']
    );

    // Aktualisiere Performance-Metriken
    $db->updatePerformanceMetrics($data['symbol']);

    // Sende Erfolgsantwort
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Trade erfolgreich ausgeführt',
        'data' => [
            'trade_id' => $tradeId,
            'order_id' => $order['data']['orderId'],
            'entry_price' => $currentPrice,
            'stop_loss' => $stopLoss,
            'take_profit' => $takeProfit,
            'analysis' => $analysis
        ]
    ]);

} catch (Exception $e) {
    // Fehlerbehandlung
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
