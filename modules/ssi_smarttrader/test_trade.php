<?php
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/src/classes/MarketAnalyzer.php');
require_once(__DIR__ . '/src/classes/BitgetAPI.php');

try {
    // Initialisiere Klassen
    $analyzer = new MarketAnalyzer();
    
    // Analysiere den Markt für ETHUSDT
    $analysis = $analyzer->analyze('ETHUSDT_UMCBL');
    
    echo "Marktanalyse für ETHUSDT:\n";
    echo "-------------------------\n";
    echo "Aktueller Preis: " . $analysis['currentPrice'] . "\n";
    echo "ADX: " . $analysis['indicators']['adx'] . "\n";
    echo "ATR %: " . $analysis['indicators']['atrPercent'] . "\n";
    echo "ROC: " . $analysis['indicators']['roc'] . "\n";
    echo "Empfehlung: " . $analysis['recommendation']['type'] . " (Konfidenz: " . $analysis['recommendation']['confidence'] . "%)\n\n";

    // Wenn wir ein starkes Signal haben, führe den Trade aus
    if ($analysis['recommendation']['confidence'] >= 60) {
        $tradeData = [
            'symbol' => 'ETHUSDT_UMCBL',
            'position_type' => $analysis['recommendation']['type'],
            'position_size' => 0.01, // Kleine Position für den Test
            'leverage' => 10,
            'entry_price' => $analysis['currentPrice'],
            'stop_loss' => $analysis['currentPrice'] - ($analysis['indicators']['atr'] * 2),
            'take_profit' => $analysis['currentPrice'] + ($analysis['indicators']['atr'] * 2)
        ];

        // Sende Trade-Request
        $ch = curl_init('http://localhost/smart8/modules/ssi_smarttrader/src/api/process_trade.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($tradeData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        echo "Trade-Ausführung:\n";
        echo "----------------\n";
        if ($httpCode === 200 && $result['success']) {
            echo "Trade erfolgreich ausgeführt!\n";
            echo "Trade ID: " . $result['data']['trade_id'] . "\n";
            echo "Order ID: " . $result['data']['order_id'] . "\n";
            echo "Eintrittspreis: " . $result['data']['entry_price'] . "\n";
            echo "Stop-Loss: " . $result['data']['stop_loss'] . "\n";
            echo "Take-Profit: " . $result['data']['take_profit'] . "\n";
        } else {
            echo "Fehler bei der Trade-Ausführung:\n";
            echo $result['message'] . "\n";
        }
    } else {
        echo "Kein ausreichend starkes Signal für einen Trade.\n";
    }

} catch (Exception $e) {
    error_log('Fehler in test_trade.php: ' . $e->getMessage());
    echo "Fehler: " . $e->getMessage() . "\n";
}
