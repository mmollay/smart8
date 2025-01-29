<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget_api.php');

header('Content-Type: application/json');

try {
    // API Credentials holen
    $stmt = $db->prepare("
        SELECT * FROM api_credentials 
        WHERE platform = 'bitget' 
        AND is_active = 1 
        ORDER BY last_used DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $cred = $stmt->get_result()->fetch_assoc();

    if (!$cred) {
        throw new Exception("Keine aktiven API Credentials gefunden");
    }

    // BitGet API initialisieren
    $bitget = new BitGetAPI($cred['api_key'], $cred['api_secret'], $cred['api_passphrase']);

    // Parameter
    $symbol = $_GET['symbol'] ?? 'BTCUSDT';
    $limit = min(intval($_GET['limit'] ?? 100), 100);
    $startTime = $_GET['startTime'] ?? null;
    $endTime = $_GET['endTime'] ?? null;

    // Daten abrufen
    $data = [
        'trades' => $bitget->getTradeHistory($symbol, $limit, $startTime, $endTime),
        'pnl' => $bitget->getPnLHistory($symbol, $limit, $startTime, $endTime)
    ];

    // Gesamtprofit berechnen
    $totalPnL = 0;
    if (isset($data['pnl']['data'])) {
        foreach ($data['pnl']['data'] as $pnl) {
            $totalPnL += floatval($pnl['profit']);
        }
    }
    $data['totalPnL'] = $totalPnL;

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
