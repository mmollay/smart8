<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget_api.php');

header('Content-Type: application/json');

try {
    // API Credentials holen
    $stmt = $db->prepare("
        SELECT ac.*, u.id as user_id 
        FROM api_credentials ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.platform = 'bitget' 
        AND ac.is_active = 1 
        ORDER BY ac.last_used DESC 
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
    $limit = min(intval($_GET['limit'] ?? 1000), 1000);
    $reset = isset($_GET['reset']) && $_GET['reset'] === '1';

    // Zeitraum: Letzte 90 Tage
    $endTime = strtotime('now') * 1000; // Unix timestamp in Millisekunden
    $startTime = strtotime('-90 days') * 1000; // 90 Tage zurück in Millisekunden

    error_log("Sync Parameters:");
    error_log("- Symbol: " . $symbol);
    error_log("- Start Time: " . date('Y-m-d H:i:s', $startTime / 1000));
    error_log("- End Time: " . date('Y-m-d H:i:s', $endTime / 1000));
    error_log("- Limit: " . $limit);
    error_log("- Reset: " . ($reset ? 'yes' : 'no'));

    // Optional: Tabellen leeren wenn reset=1
    if ($reset) {
        $db->query("DELETE FROM trades WHERE user_id = " . $cred['user_id'] . " AND symbol = '" . $db->real_escape_string(str_replace('_UMCBL', '', $symbol)) . "'");
        $db->query("DELETE FROM pnl_history WHERE user_id = " . $cred['user_id'] . " AND symbol = '" . $db->real_escape_string(str_replace('_UMCBL', '', $symbol)) . "'");
        error_log("Trades und PnL History für User " . $cred['user_id'] . " und Symbol " . $symbol . " geleert");
    }

    // Trades abrufen und speichern
    $formattedSymbol = str_replace('USDT', 'USDT_UMCBL', $symbol);
    error_log("Formatiertes Symbol für API: " . $formattedSymbol);
    
    $trades = $bitget->getAllTradeHistory($formattedSymbol, $startTime, $endTime);
    error_log("BitGet Trade History Response: " . json_encode($trades));

    if (!is_array($trades) || !isset($trades['data'])) {
        throw new Exception("Ungültige API Response für Trades: " . json_encode($trades));
    }

    $tradesList = $trades['data'];
    error_log("Anzahl Trades: " . count($tradesList));

    // Prüfe ob die Trades zum richtigen Symbol gehören
    $tradesList = array_filter($tradesList, function($trade) use ($formattedSymbol) {
        return $trade['symbol'] === $formattedSymbol;
    });
    error_log("Anzahl Trades nach Symbol-Filterung: " . count($tradesList));

    $savedTrades = 0;
    $updatedTrades = 0;

    // Prepared Statement für Trades
    $stmt = $db->prepare("
        INSERT INTO trades (
            user_id, symbol, side, size, price, fee, fee_coin,
            trade_id, order_id, bitget_timestamp, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            size = VALUES(size),
            price = VALUES(price),
            fee = VALUES(fee)
    ");

    foreach ($tradesList as $trade) {
        try {
            error_log("Verarbeite Trade: " . json_encode($trade));

            if (!is_array($trade)) {
                error_log("Trade ist kein Array, überspringe...");
                continue;
            }

            // Symbol ohne _UMCBL suffix speichern
            $tradeSymbol = str_replace('_UMCBL', '', $symbol);

            // Variablen für bind_param vorbereiten
            $userId = $cred['user_id'];
            $side = $trade['side'] ?? 'unknown';
            $size = floatval($trade['sizeQty'] ?? $trade['size'] ?? 0);
            $price = floatval($trade['price'] ?? 0);
            $fee = floatval($trade['fee'] ?? 0);
            $feeCoin = $trade['feeCoin'] ?? 'USDT';
            $tradeId = $trade['tradeId'] ?? '';
            $orderId = $trade['orderId'] ?? '';
            $timestamp = $trade['cTime'] ?? time() * 1000;

            error_log("Prepared variables: " . json_encode([
                'userId' => $userId,
                'symbol' => $tradeSymbol,
                'side' => $side,
                'size' => $size,
                'price' => $price,
                'fee' => $fee,
                'feeCoin' => $feeCoin,
                'tradeId' => $tradeId,
                'orderId' => $orderId,
                'timestamp' => $timestamp
            ]));

            $stmt->bind_param(
                "issdddsssi",
                $userId,
                $tradeSymbol,
                $side,
                $size,
                $price,
                $fee,
                $feeCoin,
                $tradeId,
                $orderId,
                $timestamp
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows == 1) {
                    $savedTrades++;
                } else if ($stmt->affected_rows == 2) {
                    $updatedTrades++;
                }
            }
        } catch (Exception $e) {
            error_log("Fehler beim Speichern des Trades: " . $e->getMessage());
            error_log("Trade data: " . json_encode($trade));
        }
    }

    // PnL History abrufen
    $formattedSymbol = str_replace('USDT', 'USDT_UMCBL', $symbol);
    error_log("Formatiertes Symbol für API: " . $formattedSymbol);

    $pnl = $bitget->getPnLHistory($formattedSymbol, $startTime, $endTime);
    error_log("BitGet PnL History Response: " . json_encode($pnl));

    if (!isset($pnl['data']) || !isset($pnl['data']['list'])) {
        throw new Exception("Ungültige API Response für PnL: " . json_encode($pnl));
    }

    $pnlList = $pnl['data']['list'];
    error_log("Anzahl PnL Einträge: " . count($pnlList));

    // Prüfe ob die PnL-Einträge zum richtigen Symbol gehören
    $pnlList = array_filter($pnlList, function ($entry) use ($formattedSymbol) {
        return $entry['symbol'] === $formattedSymbol;
    });
    error_log("Anzahl PnL Einträge nach Symbol-Filterung: " . count($pnlList));

    $savedPnL = 0;
    $updatedPnL = 0;

    // Prepared Statement für PnL
    $stmt = $db->prepare("
        INSERT INTO pnl_history (
            user_id,      -- 1. i (int)
            symbol,       -- 2. s (string)
            side,         -- 3. s (string)
            size,         -- 4. d (double)
            entry_price,  -- 5. d (double)
            exit_price,   -- 6. d (double)
            profit,       -- 7. d (double)
            net_profit,   -- 8. d (double)
            leverage,     -- 9. d (double)
            bitget_timestamp  -- 10. i (int)
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            exit_price = VALUES(exit_price),
            profit = VALUES(profit),
            net_profit = VALUES(net_profit)
    ");

    foreach ($pnlList as $entry) {
        try {
            error_log("Verarbeite PnL Eintrag: " . json_encode($entry));

            if (!is_array($entry)) {
                error_log("PnL ist kein Array, überspringe...");
                continue;
            }

            // Variablen für bind_param vorbereiten
            $params = [
                'userId' => $cred['user_id'],                    // 1. i
                'symbol' => str_replace('_UMCBL', '', $symbol),  // 2. s
                'side' => $entry['holdSide'] ?? 'unknown',      // 3. s
                'size' => floatval($entry['openTotalPos'] ?? 0),    // 4. d
                'entryPrice' => floatval($entry['openAvgPrice'] ?? 0),  // 5. d
                'exitPrice' => floatval($entry['closeAvgPrice'] ?? 0),  // 6. d
                'profit' => floatval($entry['pnl'] ?? 0),    // 7. d
                'netProfit' => floatval($entry['netProfit'] ?? 0),    // 8. d
                'leverage' => 1, // Standard-Leverage, da nicht in API-Antwort
                'timestamp' => intval($entry['utime'] ?? $entry['ctime'] ?? time() * 1000) // 10. i
            ];
            
            error_log("Prepared PnL variables: " . json_encode($params));
            
            $stmt->bind_param(
                "issddddddi", // 10 Parameter: i(1) + s(2) + d(6) + i(1) = 10
                $params['userId'],
                $params['symbol'],
                $params['side'],
                $params['size'],
                $params['entryPrice'],
                $params['exitPrice'],
                $params['profit'],
                $params['netProfit'],
                $params['leverage'],
                $params['timestamp']
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows == 1) {
                    $savedPnL++;
                } else if ($stmt->affected_rows == 2) {
                    $updatedPnL++;
                }
            }
        } catch (Exception $e) {
            error_log("Fehler beim Speichern des PnL Eintrags: " . $e->getMessage());
            error_log("PnL data: " . json_encode($entry));
        }
    }

    // Last used timestamp aktualisieren
    $stmt = $db->prepare("UPDATE api_credentials SET last_used = NOW() WHERE id = ?");
    $stmt->bind_param("i", $cred['id']);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => sprintf(
            'Trades: %d neu, %d aktualisiert. PnL: %d neu, %d aktualisiert.',
            $savedTrades,
            $updatedTrades,
            $savedPnL,
            $updatedPnL
        ),
        'debug' => [
            'trades_count' => count($tradesList),
            'pnl_count' => count($pnlList),
            'first_trade' => $tradesList[0] ?? null,
            'first_pnl' => $pnlList[0] ?? null,
            'symbol' => $symbol,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'timeRange' => [
                'start' => date('Y-m-d H:i:s', $startTime / 1000),
                'end' => date('Y-m-d H:i:s', $endTime / 1000)
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in sync_trades.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
