<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/classes/MarketData.php';
require_once __DIR__ . '/classes/Backtesting.php';
require_once __DIR__ . '/classes/TechnicalIndicators.php';

try {
    // Parameter für den Backtest
    //$symbol = 'BTCUSDT_UMCBL'; // Angepasst für BitGet
    $symbol = 'ETHUSDT_UMCBL';
    $interval = '1h';
    $period = 120; // 90 Tage
    $initialBalance = 10000; // 10.000 USDT

    // Zeitraum festlegen
    $endTime = time();
    $startTime = $endTime - ($period * 24 * 60 * 60);

    // MarketData-Instanz erstellen
    $marketData = new MarketData();

    // Historische Daten abrufen
    echo "Hole historische Daten...\n";
    $historicalData = $marketData->getHistoricalData($symbol, $interval, $startTime, $endTime);

    if (empty($historicalData)) {
        throw new Exception("Keine historischen Daten verfügbar");
    }

    echo "Anzahl der Kerzen: " . count($historicalData) . "\n";

    // Backtest durchführen
    echo "Starte Backtest...\n";
    $backtest = new Backtesting($symbol, $interval, $period, $initialBalance);
    $results = $backtest->run($historicalData);

    // Ausgabe der Backtest-Ergebnisse
    echo "\nBacktest-Ergebnisse:\n";
    echo "Gesamtanzahl Trades: " . ($results['total_trades'] ?? 0) . "\n";
    echo "Gewinnende Trades: " . ($results['winning_trades'] ?? 0) . "\n";
    echo "Verlierende Trades: " . ($results['losing_trades'] ?? 0) . "\n";
    echo "Profit Faktor: " . number_format(($results['profit_factor'] ?? 0), 2) . "\n";
    echo "Netto Gewinn: " . number_format(($results['net_profit'] ?? 0), 2) . " USDT\n";
    echo "Maximaler Drawdown: " . number_format(($results['maxDrawdown'] ?? 0), 2) . "%\n";

    // Details der einzelnen Trades
    echo "\nEinzelne Trades:\n";
    if (isset($results['trades']) && is_array($results['trades'])) {
        foreach ($results['trades'] as $trade) {
            if (isset($trade['exit_time'])) {
                echo sprintf(
                    "Entry: %s, Exit: %s, P&L: %.2f USDT\n",
                    date('Y-m-d H:i:s', (int)($trade['entry_time'] / 1000)),
                    date('Y-m-d H:i:s', (int)($trade['exit_time'] / 1000)),
                    $trade['profit_loss']
                );
            }
        }
    }

} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
