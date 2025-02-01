<?php
class Backtesting {
    private $db;
    private $symbol;
    private $interval;
    private $period;
    private $initialBalance;
    private $currentBalance;
    private $feeRate = 0.0006; // 0.06% Handelsgebühr
    private $trades = [];
    private $metrics = [];
    private $equityHistory = [];
    private $runId;
    private $marketData;
    private $technicalIndicators;
    private $currentPosition = null;
    private $positionSize = 0;
    private $entryPrice = 0;
    private $stopLoss = 0;
    private $takeProfit = 0;
    private $maxDrawdown = 0;
    private $closePrices = [];
    private $inPosition = false;
    private $positionType = null;
    private $historicalData;
    
    public function __construct($symbol, $interval, $period, $initialBalance) {
        try {
            $this->db = new mysqli('127.0.0.1', 'smart', 'Eiddswwenph21;', 'ssi_trader2', 3306);
            
            if ($this->db->connect_error) {
                throw new Exception("Verbindung fehlgeschlagen: " . $this->db->connect_error);
            }
            
            // Setze UTF-8 als Zeichensatz
            $this->db->set_charset('utf8mb4');
            
            $this->symbol = $symbol;
            $this->interval = $interval;
            $this->period = $period;
            $this->initialBalance = $initialBalance;
            $this->currentBalance = $initialBalance;
            
            // Erstelle einen neuen Backtest-Run
            $this->createBacktestRun();
            
        } catch (Exception $e) {
            error_log("Datenbankfehler: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function run($historicalData) {
        if (empty($historicalData)) {
            throw new Exception("Keine historischen Daten verfügbar");
        }
        
        $this->marketData = $historicalData;
        $this->historicalData = $historicalData;
        $this->technicalIndicators = new TechnicalIndicators();
        $this->closePrices = array_column($historicalData, 'close');
        $lastPrice = null;
        $highestBalance = $this->initialBalance;
        
        foreach ($historicalData as $i => $candle) {
            // Überspringe die ersten Kerzen, bis wir genug Daten für die Indikatoren haben
            if ($i < 26) {
                continue;
            }
            
            // Hole die letzten n Kerzen für die Indikatoren
            $lookback = array_slice($historicalData, max(0, $i - 26), 27);
            
            // Aktualisiere technische Indikatoren
            $rsi = $this->technicalIndicators->calculateRSI($lookback, 14, $candle['timestamp']);
            $macd = $this->technicalIndicators->calculateMACD($lookback, 12, 26, 9, $candle['timestamp']);
            $bb = $this->technicalIndicators->calculateBollingerBands($lookback, 20, 2, $candle['timestamp']);
            
            if ($rsi === null || $macd === null || $bb === null) {
                continue;
            }
            
            // Trading-Logik
            $data = [
                'rsi' => $rsi,
                'macd' => $macd,
                'bollinger' => $bb,
                'close' => $candle['close']
            ];
            
            $longSignal = $this->checkLongEntrySignal($candle, $rsi, $macd, $bb);
            $shortSignal = $this->checkShortEntrySignal($candle, $rsi, $macd, $bb);
            
            if ($longSignal && !$this->inPosition) {
                $this->enterLong($candle, 1.0);
                $this->inPosition = true;
                $this->positionType = 'long';
            } elseif ($shortSignal && !$this->inPosition) {
                $this->enterShort($candle, 1.0);
                $this->inPosition = true;
                $this->positionType = 'short';
            } elseif ($this->inPosition) {
                if ($this->positionType === 'long') {
                    $exitLongSignal = $this->checkExitSignal('long', $i, $this->entryPrice);
                    if ($exitLongSignal) {
                        $this->exitPosition($candle, 'exit_signal');
                        $this->inPosition = false;
                    }
                } else {
                    $exitShortSignal = $this->checkExitSignal('short', $i, $this->entryPrice);
                    if ($exitShortSignal) {
                        $this->exitPosition($candle, 'exit_signal');
                        $this->inPosition = false;
                    }
                }
            }
            
            // Prüfe Stop-Loss und Take-Profit
            if ($this->currentPosition !== null) {
                $this->checkStopLossAndTakeProfit($candle);
            }
            
            // Aktualisiere Equity-Kurve
            $this->updateEquity($candle);
            
            // Aktualisiere höchsten Kontostand
            if ($this->currentBalance > $highestBalance) {
                $highestBalance = $this->currentBalance;
            }
            
            $lastPrice = $candle['close'];
        }
        
        // Schließe offene Position am Ende des Backtests
        if ($this->currentPosition !== null) {
            $this->exitPosition($lastPrice, 'backtest_end');
        }
        
        // Berechne Metriken
        $this->calculateMetrics($highestBalance);
        
        // Aktualisiere Backtest-Run
        $this->updateBacktestRun();
        
        return [
            'trades' => $this->trades,
            'metrics' => $this->metrics,
            'equity_history' => $this->equityHistory
        ];
    }
    
    private function createBacktestRun() {
        $query = "INSERT INTO backtest_runs (symbol, interval_type, period, initial_balance, fee_rate, start_time) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
                 
        try {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare Statement fehlgeschlagen: " . $this->db->error);
            }
            
            $stmt->bind_param("ssidi", 
                $this->symbol,
                $this->interval,
                $this->period,
                $this->initialBalance,
                $this->feeRate
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute Statement fehlgeschlagen: " . $stmt->error);
            }
            
            $this->runId = $this->db->insert_id;
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Backtest-Run erstellen fehlgeschlagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function checkLongEntrySignal($candle, $rsi, $macd, $bb) {
        // RSI-Bedingungen
        $rsiOversold = $rsi < 45; // Noch weiter erhöht von 40
        
        // MACD-Bedingungen - mindestens eine muss erfüllt sein
        $macdCrossover = $macd['macd'] > $macd['signal'];
        $macdPositive = $macd['histogram'] > -1.0; // Weiter gelockert von -0.5
        $macdRising = $macd['macd'] > $this->candles[1]['macd']['macd'];
        
        // Bollinger Bands Bedingungen
        $price = $candle['close'];
        $bbSqueezing = ($bb['upper'] - $bb['lower']) / $bb['middle'] < 0.08; // Erhöht von 0.05
        $nearMiddleBand = abs($price - $bb['middle']) / $bb['middle'] < 0.02; // Erhöht von 0.01
        
        // Volumen-Bedingung
        $volumeIncreasing = $candle['volume'] > $this->candles[1]['volume'] * 1.05; // Reduziert auf 5%
        
        // Trendbestätigung
        $sma20 = $this->calculateSMA(20);
        $trendUp = $price > $sma20 * 0.995; // 0.5% Toleranz
        
        // Debug-Ausgaben
        echo "\nLong Signal Check für Kerze: " . date('Y-m-d H:i:s', $candle['timestamp']) . "\n";
        echo "Preis: " . $price . "\n";
        echo "RSI: " . $rsi . " (Oversold: " . ($rsiOversold ? "ja" : "nein") . ")\n";
        echo "MACD Crossover: " . ($macdCrossover ? "ja" : "nein") . "\n";
        echo "MACD Positiv: " . ($macdPositive ? "ja" : "nein") . "\n";
        echo "MACD Steigend: " . ($macdRising ? "ja" : "nein") . "\n";
        echo "BB Squeeze: " . ($bbSqueezing ? "ja" : "nein") . "\n";
        echo "Nahe Middle Band: " . ($nearMiddleBand ? "ja" : "nein") . "\n";
        echo "Volumen steigend: " . ($volumeIncreasing ? "ja" : "nein") . "\n";
        echo "Trend nach oben: " . ($trendUp ? "ja" : "nein") . "\n";
        
        // Signal-Logik: Mindestens 2 von 3 Hauptbedingungen müssen erfüllt sein
        $mainConditions = [
            $rsiOversold,
            ($macdCrossover || $macdPositive || $macdRising), // MACD-Bedingungen als Gruppe
            ($bbSqueezing || $nearMiddleBand) // BB-Bedingungen als Gruppe
        ];
        
        $conditionCount = array_sum($mainConditions);
        $hasSignal = $conditionCount >= 1; // Reduziert auf 1 von 3
        
        echo "Erfüllte Hauptbedingungen: " . $conditionCount . "/3\n";
        echo "Signal: " . ($hasSignal ? "JA" : "NEIN") . "\n";
        echo "Finale Entscheidung: " . (($hasSignal && $volumeIncreasing && $trendUp) ? "LONG TRADE" : "KEIN TRADE") . "\n";
        
        // Zusätzliche Bestätigung durch Volumen und Trend
        return $hasSignal && $volumeIncreasing && $trendUp;
    }
    
    private function checkShortEntrySignal($candle, $rsi, $macd, $bb) {
        // RSI-Bedingungen
        $rsiOverbought = $rsi > 55; // Gesenkt von 60
        
        // MACD-Bedingungen - mindestens eine muss erfüllt sein
        $macdCrossover = $macd['macd'] < $macd['signal'];
        $macdNegative = $macd['histogram'] < 1.0; // Gelockert von 0.5
        $macdFalling = $macd['macd'] < $this->candles[1]['macd']['macd'];
        
        // Bollinger Bands Bedingungen
        $price = $candle['close'];
        $bbSqueezing = ($bb['upper'] - $bb['lower']) / $bb['middle'] < 0.08; // Erhöht von 0.05
        $nearMiddleBand = abs($price - $bb['middle']) / $bb['middle'] < 0.02; // Erhöht von 0.01
        
        // Volumen-Bedingung
        $volumeIncreasing = $candle['volume'] > $this->candles[1]['volume'] * 1.05; // Reduziert auf 5%
        
        // Trendbestätigung
        $sma20 = $this->calculateSMA(20);
        $trendDown = $price < $sma20 * 1.005; // 0.5% Toleranz
        
        // Debug-Ausgaben
        echo "\nShort Signal Check für Kerze: " . date('Y-m-d H:i:s', $candle['timestamp']) . "\n";
        echo "Preis: " . $price . "\n";
        echo "RSI: " . $rsi . " (Overbought: " . ($rsiOverbought ? "ja" : "nein") . ")\n";
        echo "MACD Crossover: " . ($macdCrossover ? "ja" : "nein") . "\n";
        echo "MACD Negativ: " . ($macdNegative ? "ja" : "nein") . "\n";
        echo "MACD Fallend: " . ($macdFalling ? "ja" : "nein") . "\n";
        echo "BB Squeeze: " . ($bbSqueezing ? "ja" : "nein") . "\n";
        echo "Nahe Middle Band: " . ($nearMiddleBand ? "ja" : "nein") . "\n";
        echo "Volumen steigend: " . ($volumeIncreasing ? "ja" : "nein") . "\n";
        echo "Trend nach unten: " . ($trendDown ? "ja" : "nein") . "\n";
        
        // Signal-Logik: Mindestens 2 von 3 Hauptbedingungen müssen erfüllt sein
        $mainConditions = [
            $rsiOverbought,
            ($macdCrossover || $macdNegative || $macdFalling), // MACD-Bedingungen als Gruppe
            ($bbSqueezing || $nearMiddleBand) // BB-Bedingungen als Gruppe
        ];
        
        $conditionCount = array_sum($mainConditions);
        $hasSignal = $conditionCount >= 1; // Reduziert auf 1 von 3
        
        echo "Erfüllte Hauptbedingungen: " . $conditionCount . "/3\n";
        echo "Signal: " . ($hasSignal ? "JA" : "NEIN") . "\n";
        echo "Finale Entscheidung: " . (($hasSignal && $volumeIncreasing && $trendDown) ? "SHORT TRADE" : "KEIN TRADE") . "\n";
        
        // Zusätzliche Bestätigung durch Volumen und Trend
        return $hasSignal && $volumeIncreasing && $trendDown;
    }
    
    private function enterLong($candle, $stopLossPercent = 1.0) {
        $price = $candle['close'];
        
        // Berechne Position Size basierend auf adaptivem Risiko
        $volatility = $this->calculateVolatility(14);
        $riskPercent = min(0.03, max(0.015, $volatility * 2.5)); // Erhöhtes Risiko (1.5-3% statt 1-2%)
        
        // Stop-Loss 3% unter dem Eintrittspreis
        $stopLossPrice = $price * 0.97;
        $riskPerShare = $price - $stopLossPrice;
        $riskAmount = $this->currentBalance * $riskPercent;
        $positionSize = $riskAmount / $riskPerShare;
        
        // Erhöhe maximale Position Size auf 100% des Kapitals
        $maxPositionSize = $this->currentBalance / $price;
        $positionSize = min($positionSize, $maxPositionSize);
        
        $this->positionSize = $positionSize * $price;
        $this->entryPrice = $price;
        $this->currentPosition = 'long';
        
        // Setze Stop-Loss und Take-Profit (2:1 Risk/Reward)
        $this->stopLoss = $stopLossPrice;
        $this->takeProfit = $price * 1.04; // 4% Take-Profit
        
        // Speichere Trade in der Datenbank
        $query = "INSERT INTO backtest_trades (run_id, entry_time, type, entry_price, position_size) 
                 VALUES (?, ?, 'long', ?, ?)";
        $stmt = $this->db->prepare($query);
        $entryTime = $candle['timestamp'];
        $stmt->bind_param("iidi", $this->runId, $entryTime, $price, $this->positionSize);
        $stmt->execute();
        
        // Speichere Trade auch im lokalen Array
        $this->trades[] = [
            'entry_time' => $entryTime,
            'entry_price' => $price,
            'type' => 'long',
            'size' => $this->positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        
        echo sprintf(
            "Long Position eröffnet - Preis: %.2f, Size: %.2f, Stop Loss: %.2f, Take Profit: %.2f\n",
            $price,
            $this->positionSize,
            $this->stopLoss,
            $this->takeProfit
        );
    }
    
    private function enterShort($candle, $stopLossPercent = 1.0) {
        $price = $candle['close'];
        
        // Berechne Position Size basierend auf adaptivem Risiko
        $volatility = $this->calculateVolatility(14);
        $riskPercent = min(0.03, max(0.015, $volatility * 2.5)); // Erhöhtes Risiko (1.5-3% statt 1-2%)
        
        // Stop-Loss 3% über dem Eintrittspreis
        $stopLossPrice = $price * 1.03;
        $riskPerShare = $stopLossPrice - $price;
        $riskAmount = $this->currentBalance * $riskPercent;
        $positionSize = $riskAmount / $riskPerShare;
        
        // Erhöhe maximale Position Size auf 100% des Kapitals
        $maxPositionSize = $this->currentBalance / $price;
        $positionSize = min($positionSize, $maxPositionSize);
        
        $this->positionSize = $positionSize * $price;
        $this->entryPrice = $price;
        $this->currentPosition = 'short';
        
        // Setze Stop-Loss und Take-Profit (2:1 Risk/Reward)
        $this->stopLoss = $stopLossPrice;
        $this->takeProfit = $price * 0.96; // 4% Take-Profit
        
        // Speichere Trade in der Datenbank
        $query = "INSERT INTO backtest_trades (run_id, entry_time, type, entry_price, position_size) 
                 VALUES (?, ?, 'short', ?, ?)";
        $stmt = $this->db->prepare($query);
        $entryTime = $candle['timestamp'];
        $stmt->bind_param("iidi", $this->runId, $entryTime, $price, $this->positionSize);
        $stmt->execute();
        
        // Speichere Trade auch im lokalen Array
        $this->trades[] = [
            'entry_time' => $entryTime,
            'entry_price' => $price,
            'type' => 'short',
            'size' => $this->positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        
        echo sprintf(
            "Short Position eröffnet - Preis: %.2f, Size: %.2f, Stop Loss: %.2f, Take Profit: %.2f\n",
            $price,
            $this->positionSize,
            $this->stopLoss,
            $this->takeProfit
        );
    }
    
    private function checkStopLossAndTakeProfit($candle) {
        $price = $candle['close'];
        
        if ($this->currentPosition === 'long') {
            // Trailing Stop Loss - wird nachgezogen wenn der Preis steigt
            if ($price > $this->entryPrice) {
                $newStopLoss = $price * 0.97; // 3% unter aktuellem Preis
                if ($newStopLoss > $this->stopLoss) {
                    $this->stopLoss = $newStopLoss;
                    echo sprintf("Trailing Stop Loss angepasst: %.2f\n", $this->stopLoss);
                }
            }
            
            if ($price <= $this->stopLoss) {
                $this->exitPosition($candle, 'stop_loss');
            }
            else if ($price >= $this->takeProfit) {
                $this->exitPosition($candle, 'take_profit');
            }
        } else {
            // Trailing Stop Loss - wird nachgezogen wenn der Preis fällt
            if ($price < $this->entryPrice) {
                $newStopLoss = $price * 1.03; // 3% über aktuellem Preis
                if ($newStopLoss < $this->stopLoss) {
                    $this->stopLoss = $newStopLoss;
                    echo sprintf("Trailing Stop Loss angepasst: %.2f\n", $this->stopLoss);
                }
            }
            
            if ($price >= $this->stopLoss) {
                $this->exitPosition($candle, 'stop_loss');
            }
            else if ($price <= $this->takeProfit) {
                $this->exitPosition($candle, 'take_profit');
            }
        }
    }
    
    private function exitPosition($candle, $reason) {
        if ($this->currentPosition === null) {
            return;
        }
        
        $price = $candle['close'];
        $lastTrade = &$this->trades[count($this->trades) - 1];
        
        // Berechne Gewinn/Verlust
        $profit = 0;
        if ($lastTrade['type'] === 'long') {
            $profit = ($price - $lastTrade['entry_price']) * $lastTrade['size'];
        } else {
            $profit = ($lastTrade['entry_price'] - $price) * $lastTrade['size'];
        }
        $profitPercent = ($price / $lastTrade['entry_price'] - 1) * 100;
        
        // Aktualisiere Kontostand
        $this->currentBalance += $profit;
        
        // Aktualisiere Trade in der Datenbank
        $query = "UPDATE backtest_trades 
                 SET exit_time = ?, exit_price = ?, profit_loss = ?, exit_reason = ? 
                 WHERE run_id = ? AND entry_time = ?";
        $stmt = $this->db->prepare($query);
        $exitTime = $candle['timestamp'];
        $stmt->bind_param("iddsii", 
            $exitTime, 
            $price, 
            $profit, 
            $reason,
            $this->runId,
            $lastTrade['entry_time']
        );
        $stmt->execute();
        
        // Aktualisiere Trade im lokalen Array
        $lastTrade['exit_price'] = $price;
        $lastTrade['exit_time'] = $exitTime;
        $lastTrade['exit_reason'] = $reason;
        $lastTrade['profit'] = $profit;
        $lastTrade['profit_percent'] = $profitPercent;
        
        echo sprintf(
            "Position geschlossen (%s) - Preis: %.2f, Zeit: %s, Profit: %.2f USDT (%.2f%%)\n",
            $reason,
            $price,
            date('Y-m-d H:i:s', $exitTime),
            $profit,
            $profitPercent
        );
        
        // Reset Position
        $this->currentPosition = null;
        $this->positionSize = 0;
        $this->entryPrice = 0;
        $this->stopLoss = 0;
        $this->takeProfit = 0;
    }
    
    private function updateEquity($candle) {
        $equity = $this->initialBalance;
        $this->equityHistory = [];
        $maxEquity = $equity;
        $maxDrawdown = 0;
        
        foreach ($this->marketData as $timestamp => $candle) {
            // Position offen
            if ($this->currentPosition) {
                $unrealizedPnl = ($candle['close'] - $this->entryPrice) * $this->positionSize;
                $equity = $this->initialBalance + $unrealizedPnl;
            }
            
            // Aktualisiere Maximal-Equity und Drawdown
            if ($equity > $maxEquity) {
                $maxEquity = $equity;
            }
            
            $currentDrawdown = ($maxEquity - $equity) / $maxEquity * 100;
            if ($currentDrawdown > $maxDrawdown) {
                $maxDrawdown = $currentDrawdown;
            }
            
            // Speichere Equity-Historie
            $this->equityHistory[$timestamp] = [
                'equity' => $equity,
                'drawdown' => $currentDrawdown,
                'price' => $candle['close'],
                'position' => $this->currentPosition ? 'long' : 'none'
            ];
        }
        
        // Speichere finale Statistiken
        $this->metrics['finalEquity'] = $equity;
        $this->metrics['maxDrawdown'] = $maxDrawdown;
        $this->metrics['totalReturn'] = (($equity - $this->initialBalance) / $this->initialBalance) * 100;
        
        // Debug Ausgabe
        echo "\nEquity Verlauf:\n";
        foreach ($this->equityHistory as $timestamp => $data) {
            $date = date('Y-m-d H:i', $timestamp);
            echo "$date - Equity: " . number_format($data['equity'], 2) . 
                 " USDT, DD: " . number_format($data['drawdown'], 2) . 
                 "%, Position: " . $data['position'] . "\n";
        }
    }
    
    private function calculateMetrics($highestBalance) {
        $totalTrades = count($this->trades);
        $winningTrades = 0;
        $losingTrades = 0;
        $totalProfit = 0;
        $totalLoss = 0;
        
        foreach ($this->trades as $trade) {
            if (isset($trade['profit'])) {
                if ($trade['profit'] > 0) {
                    $winningTrades++;
                    $totalProfit += $trade['profit'];
                } else {
                    $losingTrades++;
                    $totalLoss += abs($trade['profit']);
                }
            }
        }
        
        $netProfit = $this->currentBalance - $this->initialBalance;
        $profitFactor = $totalLoss > 0 ? $totalProfit / $totalLoss : 0;
        
        $this->metrics['total_trades'] = $totalTrades;
        $this->metrics['winning_trades'] = $winningTrades;
        $this->metrics['losing_trades'] = $losingTrades;
        $this->metrics['profit_factor'] = $profitFactor;
        $this->metrics['net_profit'] = $netProfit;
    }
    
    private function updateBacktestRun() {
        $query = "UPDATE backtest_runs 
                 SET end_time = NOW(),
                     total_trades = ?,
                     winning_trades = ?,
                     losing_trades = ?,
                     profit_factor = ?,
                     net_profit = ?,
                     max_drawdown = ?
                 WHERE id = ?";
                 
        try {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare Statement fehlgeschlagen: " . $this->db->error);
            }
            
            $stmt->bind_param("iiidddi",
                $this->metrics['total_trades'],
                $this->metrics['winning_trades'],
                $this->metrics['losing_trades'],
                $this->metrics['profit_factor'],
                $this->metrics['net_profit'],
                $this->metrics['maxDrawdown'],
                $this->runId
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute Statement fehlgeschlagen: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Backtest-Run aktualisieren fehlgeschlagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function calculateVolatility($period) {
        $prices = array_column($this->marketData, 'close');
        $volatility = 0;
        
        for ($i = 0; $i < count($prices) - $period; $i++) {
            $range = max(array_slice($prices, $i, $period)) - min(array_slice($prices, $i, $period));
            $volatility += $range;
        }
        
        return $volatility / (count($prices) - $period);
    }
    
    private function calculateATR($period) {
        $tr = [];
        $high = array_column($this->marketData, 'high');
        $low = array_column($this->marketData, 'low');
        $close = array_column($this->marketData, 'close');
        
        for ($i = 0; $i < count($this->marketData) - 1; $i++) {
            $hl = abs($high[$i] - $low[$i]);
            $hc = abs($high[$i] - $close[$i]);
            $lc = abs($low[$i] - $close[$i]);
            $tr[] = max($hl, $hc, $lc);
        }
        
        return array_sum(array_slice($tr, -$period)) / $period;
    }
    
    private function calculateSMA($period) {
        $prices = array_column($this->marketData, 'close');
        return array_sum(array_slice($prices, -$period)) / $period;
    }
    
    /**
     * Gibt die Backtest-Metriken zurück
     * @return array
     */
    public function getMetrics() {
        return $this->metrics;
    }
}
