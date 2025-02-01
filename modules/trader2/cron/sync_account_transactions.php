<?php
require_once(__DIR__ . '/../t_config.php');
require_once(__DIR__ . '/../bitget/bitget_api.php');

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

    // Zeitraum: Letzte 90 Tage in 7-Tage-Blöcken
    $endTime = strtotime('now') * 1000;
    $startTime = strtotime('-90 days') * 1000;
    $sevenDays = 7 * 24 * 60 * 60 * 1000; // 7 Tage in Millisekunden

    $allTrades = [];
    $currentStartTime = $startTime;

    while ($currentStartTime < $endTime) {
        $currentEndTime = min($currentStartTime + $sevenDays, $endTime);

        error_log("Sync Parameters für Block:");
        error_log("- Start Time: " . date('Y-m-d H:i:s', $currentStartTime / 1000));
        error_log("- End Time: " . date('Y-m-d H:i:s', $currentEndTime / 1000));

        // Account Bills für diesen Zeitblock abrufen
        $response = $bitget->getAccountBills($currentStartTime, $currentEndTime);
        error_log("BitGet Account Bills Response: " . json_encode($response));

        if (isset($response['data']['fillList']) && is_array($response['data']['fillList'])) {
            $allTrades = array_merge($allTrades, $response['data']['fillList']);
        }

        // Zum nächsten Block
        $currentStartTime += $sevenDays;
        
        // Kleine Pause zwischen den Anfragen
        usleep(500000); // 500ms Pause
    }

    error_log("Gesamtanzahl Trades: " . count($allTrades));

    $savedTransactions = 0;
    $updatedTransactions = 0;

    // Prepared Statement für Transaktionen
    $stmt = $db->prepare("
        INSERT INTO account_transactions (
            user_id, type, amount, currency, status,
            transaction_id, bitget_timestamp, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            amount = VALUES(amount),
            status = VALUES(status)
    ");

    foreach ($allTrades as $trade) {
        try {
            error_log("Verarbeite Trade: " . json_encode($trade));

            if (!is_array($trade)) {
                error_log("Trade ist kein Array, überspringe...");
                continue;
            }

            // Typ und Betrag basierend auf Profit bestimmen
            $profit = floatval($trade['profit'] ?? 0);
            $type = $profit >= 0 ? 'deposit' : 'withdrawal';
            $amount = abs($profit);
            
            // Variablen für bind_param vorbereiten
            $userId = $cred['user_id'];
            $currency = 'USDT';
            $status = 'completed';
            $transactionId = $trade['tradeId'] ?? '';
            $timestamp = $trade['cTime'] ?? time() * 1000;

            error_log("Prepared variables: " . json_encode([
                'userId' => $userId,
                'type' => $type,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $status,
                'transactionId' => $transactionId,
                'timestamp' => $timestamp
            ]));

            $stmt->bind_param(
                "isdssss",
                $userId,
                $type,
                $amount,
                $currency,
                $status,
                $transactionId,
                $timestamp
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows == 1) {
                    $savedTransactions++;
                } else if ($stmt->affected_rows == 2) {
                    $updatedTransactions++;
                }
            }
        } catch (Exception $e) {
            error_log("Fehler beim Speichern der Transaktion: " . $e->getMessage());
            error_log("Trade data: " . json_encode($trade));
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Transaktionen: {$savedTransactions} neu, {$updatedTransactions} aktualisiert.",
        'debug' => [
            'trades_count' => count($allTrades),
            'first_trade' => $allTrades[0] ?? null,
            'timeRange' => [
                'start' => date('Y-m-d H:i:s', $startTime / 1000),
                'end' => date('Y-m-d H:i:s', $endTime / 1000)
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in sync_account_transactions.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
