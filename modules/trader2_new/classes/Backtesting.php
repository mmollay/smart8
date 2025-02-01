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
    private $maxBalance = 0;
    private $indicators = [];
    private $candles;
    private $totalTrades = 0;
    private $winningTrades = 0;
    private $losingTrades = 0;
    private $totalProfit = 0;
    private $totalLoss = 0;
    
    public function __construct($symbol, $interval, $period, $initialBalance) {
        try {
            require_once __DIR__ . '/../config/t_config.php';
            
            // Datenbankverbindung herstellen
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->db->connect_error) {
                throw new Exception("Verbindungsfehler: " . $this->db->connect_error);
            }
            
            // Stelle sicher, dass die Tabellen existieren
            $this->createTables();
            
            $this->symbol = $symbol;
            $this->interval = $interval;
            $this->period = $period;
            $this->initialBalance = $initialBalance;
            $this->currentBalance = $initialBalance;
            $this->maxBalance = $initialBalance;
            
            // Initialisiere Statistik-Variablen
            $this->winningTrades = 0;
            $this->losingTrades = 0;
            $this->totalProfit = 0;
            $this->totalLoss = 0;
            
            // Erstelle einen neuen Backtest-Run
            $this->createBacktestRun();
            
        } catch (Exception $e) {
            error_log("Fehler beim Initialisieren des Backtests: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function createTables() {
        // Lösche existierende Tabellen
        $dropQueries = [
            "DROP TABLE IF EXISTS backtest_equity",
            "DROP TABLE IF EXISTS backtest_trades",
            "DROP TABLE IF EXISTS backtest_runs"
        ];
        
        foreach ($dropQueries as $query) {
            if (!$this->db->query($query)) {
                throw new Exception("Fehler beim Löschen der Tabellen: " . $this->db->error);
            }
        }
        
        $queries = [
            "CREATE TABLE IF NOT EXISTS backtest_runs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                symbol VARCHAR(20) NOT NULL,
                interval_type VARCHAR(10) NOT NULL,
                period INT NOT NULL,
                initial_balance DECIMAL(20,8) NOT NULL,
                fee_rate DECIMAL(10,8) NOT NULL,
                start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                end_time TIMESTAMP NULL,
                total_trades INT NOT NULL DEFAULT 0,
                winning_trades INT NOT NULL DEFAULT 0,
                losing_trades INT NOT NULL DEFAULT 0,
                profit_factor DECIMAL(10,4) NULL,
                net_profit DECIMAL(20,8) NULL,
                max_drawdown DECIMAL(10,4) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            
            "CREATE TABLE IF NOT EXISTS backtest_trades (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                run_id BIGINT UNSIGNED NOT NULL,
                entry_time BIGINT NOT NULL,
                exit_time BIGINT NULL,
                type ENUM('long', 'short') NOT NULL,
                entry_price DECIMAL(20,8) NOT NULL,
                exit_price DECIMAL(20,8) NULL,
                position_size DECIMAL(20,8) NOT NULL,
                stop_loss DECIMAL(20,8) NOT NULL,
                take_profit DECIMAL(20,8) NOT NULL,
                profit_loss DECIMAL(20,8) NULL,
                exit_reason VARCHAR(50) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (run_id) REFERENCES backtest_runs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            
            "CREATE TABLE IF NOT EXISTS backtest_equity (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                run_id BIGINT UNSIGNED NOT NULL,
                timestamp BIGINT NOT NULL,
                equity DECIMAL(20,8) NOT NULL,
                drawdown DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (run_id) REFERENCES backtest_runs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];
        
        foreach ($queries as $query) {
            if (!$this->db->query($query)) {
                throw new Exception("Fehler beim Erstellen der Tabellen: " . $this->db->error);
            }
        }
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
    
    public function run($historicalData) {
        $this->historicalData = $historicalData;
        $this->candles = $historicalData;
        
        // Initialisiere Indikatoren
        $this->initializeIndicators();
        
        foreach ($historicalData as $i => $candle) {
            if ($i < 20) continue;
            
            // Überprüfe Long und Short Signale
            $longSignal = $this->checkLongSignal($candle);
            $shortSignal = $this->checkShortSignal($candle);
            
            if ($longSignal && !$this->inPosition) {
                $this->enterLong($candle);
            } elseif ($shortSignal && !$this->inPosition) {
                $this->enterShort($candle);
            } elseif ($this->inPosition) {
                $result = $this->checkPositionExit($candle);
                if ($result !== null) {
                    if ($result > 0) {
                        $this->winningTrades++;
                        $this->totalProfit += $result;
                    } else {
                        $this->losingTrades++;
                        $this->totalLoss += abs($result);
                    }
                }
            }
            
            // Aktualisiere Equity
            $this->updateEquity($candle);
        }
        
        $profitFactor = $this->totalLoss != 0 ? $this->totalProfit / abs($this->totalLoss) : 0;
        
        return [
            'total_trades' => $this->totalTrades,
            'winning_trades' => $this->winningTrades,
            'losing_trades' => $this->losingTrades,
            'profit_factor' => $profitFactor,
            'net_profit' => $this->totalProfit + $this->totalLoss
        ];
    }
    
    private function initializeIndicators() {
        $this->indicators = [
            'ADX' => [],
            '+DI' => [],
            '-DI' => [],
            'ROC' => [],
            'Volume' => [],
        ];
        
        // Berechne ADX, DI und andere Indikatoren
        foreach ($this->historicalData as $candle) {
            $timestamp = $candle['timestamp'];
            
            // ADX und DI Berechnung
            $adx = $this->calculateADX($timestamp);
            $this->indicators['ADX'][$timestamp] = $adx['adx'];
            $this->indicators['+DI'][$timestamp] = $adx['+di'];
            $this->indicators['-DI'][$timestamp] = $adx['-di'];
            
            // ROC Berechnung (14 Perioden)
            $this->indicators['ROC'][$timestamp] = $this->calculateROC($timestamp, 14);
            
            // Volumen-Profil
            $this->indicators['Volume'][$timestamp] = $this->calculateVolumeProfile($timestamp);
        }
    }
    
    private function calculateADX($timestamp) {
        $period = 14;
        $currentIndex = array_search($timestamp, array_column($this->historicalData, 'timestamp'));
        if ($currentIndex < $period) {
            return ['adx' => 0, '+di' => 0, '-di' => 0];
        }
        
        $data = array_slice($this->historicalData, $currentIndex - $period, $period + 1);
        
        // Berechne True Range und Directional Movement
        $tr = [];
        $plusDM = [];
        $minusDM = [];
        
        for ($i = 1; $i < count($data); $i++) {
            $high = $data[$i]['high'];
            $low = $data[$i]['low'];
            $prevHigh = $data[$i-1]['high'];
            $prevLow = $data[$i-1]['low'];
            $prevClose = $data[$i-1]['close'];
            
            // True Range
            $tr[] = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low - $prevClose)
            );
            
            // Directional Movement
            $upMove = $high - $prevHigh;
            $downMove = $prevLow - $low;
            
            if ($upMove > $downMove && $upMove > 0) {
                $plusDM[] = $upMove;
            } else {
                $plusDM[] = 0;
            }
            
            if ($downMove > $upMove && $downMove > 0) {
                $minusDM[] = $downMove;
            } else {
                $minusDM[] = 0;
            }
        }
        
        // Berechne Smoothed Values
        $trSum = array_sum($tr);
        $plusDMSum = array_sum($plusDM);
        $minusDMSum = array_sum($minusDM);
        
        $plusDI = ($plusDMSum / $trSum) * 100;
        $minusDI = ($minusDMSum / $trSum) * 100;
        
        // Berechne ADX
        $dx = abs($plusDI - $minusDI) / ($plusDI + $minusDI) * 100;
        
        return [
            'adx' => $dx,
            '+di' => $plusDI,
            '-di' => $minusDI
        ];
    }
    
    private function calculateKeltnerChannels($candles, $period = 20, $multiplier = 2) {
        $closes = array_column($candles, 'close');
        $highs = array_column($candles, 'high');
        $lows = array_column($candles, 'low');
        
        // Berechne EMA für Mittellinie
        $middle = $this->calculateEMA($closes, $period);
        
        // Berechne Average True Range (ATR)
        $trueRanges = array();
        for ($i = 1; $i < count($candles); $i++) {
            $tr1 = $highs[$i] - $lows[$i];
            $tr2 = abs($highs[$i] - $closes[$i-1]);
            $tr3 = abs($lows[$i] - $closes[$i-1]);
            $trueRanges[] = max($tr1, $tr2, $tr3);
        }
        $atr = $this->calculateEMA($trueRanges, $period);
        
        // Berechne Bänder
        $upper = $middle + ($multiplier * $atr);
        $lower = $middle - ($multiplier * $atr);
        
        return [
            'middle' => $middle,
            'upper' => $upper,
            'lower' => $lower
        ];
    }

    private function calculateROC($timestamp, $period) {
        $currentIndex = array_search($timestamp, array_column($this->historicalData, 'timestamp'));
        if ($currentIndex < $period) {
            return 0;
        }
        
        $currentPrice = $this->historicalData[$currentIndex]['close'];
        $previousPrice = $this->historicalData[$currentIndex - $period]['close'];
        
        if ($previousPrice == 0) {
            return 0;
        }
        
        return (($currentPrice - $previousPrice) / $previousPrice) * 100;
    }

    private function calculateVolumeProfile($timestamp) {
        $period = 20;
        $currentIndex = array_search($timestamp, array_column($this->historicalData, 'timestamp'));
        if ($currentIndex < $period) {
            return 0;
        }
        
        $data = array_slice($this->historicalData, $currentIndex - $period + 1, $period);
        $volumes = array_column($data, 'volume');
        $avgVolume = array_sum($volumes) / count($volumes);
        
        return $avgVolume > 0 ? $this->historicalData[$currentIndex]['volume'] / $avgVolume : 0;
    }

    private function checkLongSignal($candle) {
        $adx = $this->indicators['ADX'][$candle['timestamp']];
        $plusDI = $this->indicators['+DI'][$candle['timestamp']];
        $minusDI = $this->indicators['-DI'][$candle['timestamp']];
        $roc = $this->indicators['ROC'][$candle['timestamp']];
        $volume = $this->indicators['Volume'][$candle['timestamp']];
        
        // Verbesserte ADX-Bedingungen
        $adxStrong = $adx > 25 && $adx < 50; // ADX nicht zu extrem
        $diSpread = $plusDI - $minusDI;
        $diValid = $diSpread > 5; // Mindestabstand zwischen +DI und -DI
        
        // Volatilitätscheck
        $atr = $this->calculateATR($candle['timestamp']);
        $volatilityOk = $atr < ($candle['close'] * 0.02); // ATR unter 2% vom Preis
        
        // Momentum-Bestätigung
        $rocValid = $roc > 0.5 && $roc < 10; // ROC in gesundem Bereich
        
        // Volumen-Bestätigung
        $volumeValid = $volume > 0.3; // Mindestvolumen
        
        return $adxStrong && $diValid && $volatilityOk && $rocValid && $volumeValid;
    }
    
    private function checkShortSignal($candle) {
        $adx = $this->indicators['ADX'][$candle['timestamp']];
        $plusDI = $this->indicators['+DI'][$candle['timestamp']];
        $minusDI = $this->indicators['-DI'][$candle['timestamp']];
        $roc = $this->indicators['ROC'][$candle['timestamp']];
        $volume = $this->indicators['Volume'][$candle['timestamp']];
        
        // Verbesserte ADX-Bedingungen
        $adxStrong = $adx > 25 && $adx < 50;
        $diSpread = $minusDI - $plusDI;
        $diValid = $diSpread > 5;
        
        // Volatilitätscheck
        $atr = $this->calculateATR($candle['timestamp']);
        $volatilityOk = $atr < ($candle['close'] * 0.02);
        
        // Momentum-Bestätigung
        $rocValid = $roc < -0.5 && $roc > -10;
        
        // Volumen-Bestätigung
        $volumeValid = $volume > 0.3;
        
        return $adxStrong && $diValid && $volatilityOk && $rocValid && $volumeValid;
    }

    private function calculateStopLoss($candle, $type) {
        $atr = $this->calculateATR($candle['timestamp']);
        $multiplier = 1.5; // Dynamischer Faktor basierend auf Volatilität
        
        if ($atr > ($candle['close'] * 0.015)) { // Bei höherer Volatilität
            $multiplier = 2.0;
        }
        
        if ($type === 'long') {
            return $candle['close'] - ($atr * $multiplier);
        } else {
            return $candle['close'] + ($atr * $multiplier);
        }
    }
    
    private function calculateTakeProfit($candle, $type, $stopLoss) {
        $riskAmount = abs($candle['close'] - $stopLoss);
        $rewardMultiplier = 2.0; // Mindest-RRR von 1:2
        
        // Erhöhe RRR bei starkem Trend
        $adx = $this->indicators['ADX'][$candle['timestamp']];
        if ($adx > 35) {
            $rewardMultiplier = 2.5;
        }
        
        if ($type === 'long') {
            return $candle['close'] + ($riskAmount * $rewardMultiplier);
        } else {
            return $candle['close'] - ($riskAmount * $rewardMultiplier);
        }
    }
    
    private function calculatePositionSize($candle) {
        $atr = $this->calculateATR($candle['timestamp']);
        $volatilityRisk = $atr / $candle['close'];
        $baseSize = $this->currentBalance * 0.1; // Basis: 10% des Kapitals
        
        // Reduziere Position bei hoher Volatilität
        if ($volatilityRisk > 0.02) {
            $baseSize *= 0.7;
        }
        
        // Erhöhe Position bei starkem Trend
        $adx = $this->indicators['ADX'][$candle['timestamp']];
        if ($adx > 30 && $adx < 45) {
            $baseSize *= 1.2;
        }
        
        return $baseSize;
    }
    
    private function calculateATR($timestamp) {
        // Implementiere ATR-Berechnung
        $period = 14;
        $atr = 0;
        $trueRanges = [];
        
        for ($i = 0; $i < $period; $i++) {
            $currentIndex = array_search($timestamp, array_keys($this->candles)) - $i;
            if ($currentIndex < 1) break;
            
            $current = array_values($this->candles)[$currentIndex];
            $previous = array_values($this->candles)[$currentIndex - 1];
            
            $tr1 = abs($current['high'] - $current['low']);
            $tr2 = abs($current['high'] - $previous['close']);
            $tr3 = abs($current['low'] - $previous['close']);
            
            $trueRanges[] = max($tr1, $tr2, $tr3);
        }
        
        if (count($trueRanges) > 0) {
            $atr = array_sum($trueRanges) / count($trueRanges);
        }
        
        return $atr;
    }
    
    private function checkPositionExit($candle) {
        if ($this->currentPosition === null) {
            return null;
        }
        
        $pnl = 0;
        $shouldExit = false;
        $exitReason = '';
        $closePrice = $candle['close'];

        if ($this->currentPosition['type'] === 'long') {
            // Berechne PnL als Prozentsatz vom Einsatz
            $pnl = ($closePrice - $this->currentPosition['entry_price']) / $this->currentPosition['entry_price'] * 100;
            
            // Prüfe Stop Loss und Take Profit
            if ($closePrice <= $this->currentPosition['stop_loss']) {
                $shouldExit = true;
                $exitReason = 'stop_loss';
            } elseif ($closePrice >= $this->currentPosition['take_profit']) {
                $shouldExit = true;
                $exitReason = 'take_profit';
            }
            // Prüfe auf Trendumkehr
            elseif ($this->indicators['-DI'][$candle['timestamp']] > $this->indicators['+DI'][$candle['timestamp']]) {
                $shouldExit = true;
                $exitReason = 'trend_reversal';
            }
        } else {
            // Berechne PnL als Prozentsatz vom Einsatz für Short-Positionen
            $pnl = ($this->currentPosition['entry_price'] - $closePrice) / $this->currentPosition['entry_price'] * 100;
            
            // Prüfe Stop Loss und Take Profit
            if ($closePrice >= $this->currentPosition['stop_loss']) {
                $shouldExit = true;
                $exitReason = 'stop_loss';
            } elseif ($closePrice <= $this->currentPosition['take_profit']) {
                $shouldExit = true;
                $exitReason = 'take_profit';
            }
            // Prüfe auf Trendumkehr
            elseif ($this->indicators['+DI'][$candle['timestamp']] > $this->indicators['-DI'][$candle['timestamp']]) {
                $shouldExit = true;
                $exitReason = 'trend_reversal';
            }
        }

        if ($shouldExit) {
            // Konvertiere PnL von Prozent in absolute Werte
            $absolutePnl = $this->currentPosition['position_size'] * ($pnl / 100);
            
            // Aktualisiere die Position
            $this->currentBalance += $absolutePnl;
            
            // Finde den letzten Trade und aktualisiere ihn
            $tradeIndex = count($this->trades) - 1;
            $this->trades[$tradeIndex]['exit_time'] = $candle['timestamp'];
            $this->trades[$tradeIndex]['exit_price'] = $closePrice;
            $this->trades[$tradeIndex]['profit_loss'] = $absolutePnl;
            $this->trades[$tradeIndex]['exit_reason'] = $exitReason;
            
            // Aktualisiere Gewinn/Verlust-Statistiken
            if ($absolutePnl > 0) {
                $this->winningTrades++;
                $this->totalProfit += $absolutePnl;
            } else {
                $this->losingTrades++;
                $this->totalLoss += abs($absolutePnl);
            }
            
            // Berechne Drawdown
            if ($this->currentBalance > $this->maxBalance) {
                $this->maxBalance = $this->currentBalance;
            }
            $drawdown = 0;
            if ($this->maxBalance > 0) {
                $drawdown = (($this->maxBalance - $this->currentBalance) / $this->maxBalance) * 100;
            }
            
            // Speichere die Position in der Datenbank
            $query = "UPDATE backtest_trades 
                     SET exit_time = ?, 
                         exit_price = ?, 
                         profit_loss = ?, 
                         exit_reason = ? 
                     WHERE run_id = ? 
                     AND entry_time = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iddsii", 
                $this->trades[$tradeIndex]['exit_time'],
                $this->trades[$tradeIndex]['exit_price'],
                $this->trades[$tradeIndex]['profit_loss'],
                $this->trades[$tradeIndex]['exit_reason'],
                $this->runId,
                $this->trades[$tradeIndex]['entry_time']
            );
            $stmt->execute();
            
            // Speichere Equity-Verlauf
            $this->saveEquity($candle['timestamp'], $this->currentBalance, $drawdown);
            
            // Lösche aktuelle Position
            $this->currentPosition = null;
            
            echo sprintf(
                "Position geschlossen - Preis: %.2f, P&L: %.2f USDT (%.2f%%)\n",
                $closePrice,
                $absolutePnl,
                $pnl
            );
            
            return $absolutePnl;
        }
        
        return null;
    }
    
    private function updateEquity($candle) {
        try {
            $equity = $this->currentBalance;
            
            if ($this->currentPosition) {
                $price = $candle['close'];
                if ($this->currentPosition['type'] === 'long') {
                    $profitLoss = $this->currentPosition['position_size'] * ($price / $this->currentPosition['entry_price'] - 1);
                } else {
                    $profitLoss = $this->currentPosition['position_size'] * ($this->currentPosition['entry_price'] / $price - 1);
                }
                $equity += $profitLoss;
            }
            
            // Aktualisiere maximale Balance
            if ($equity > $this->maxBalance) {
                $this->maxBalance = $equity;
            }
            
            // Berechne Drawdown
            $drawdown = 0;
            if ($this->maxBalance > 0) {
                $drawdown = (($this->maxBalance - $equity) / $this->maxBalance) * 100;
            }
            
            // Speichere den Equity-Verlauf
            $this->saveEquity($candle['timestamp'], $equity, $drawdown);
            
            echo sprintf(
                "%s - Equity: %.2f USDT, DD: %.2f%%, Position: %s\n",
                date('Y-m-d H:i', $candle['timestamp']),
                $equity,
                $drawdown,
                $this->currentPosition ? $this->currentPosition['type'] : 'none'
            );
        } catch (Exception $e) {
            error_log("Fehler beim Aktualisieren der Equity: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function saveEquity($timestamp, $equity, $drawdown) {
        try {
            $query = "INSERT INTO backtest_equity (run_id, timestamp, equity, drawdown) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iidd", $this->runId, $timestamp, $equity, $drawdown);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Fehler beim Speichern der Equity: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function enterLong($candle) {
        $price = $candle['close'];
        
        // Verwende nur 10% der Balance für jeden Trade
        $positionSize = $this->calculatePositionSize($candle);
        
        // Berechne Stop-Loss und Take-Profit
        $this->stopLoss = $this->calculateStopLoss($candle, 'long');
        $this->takeProfit = $this->calculateTakeProfit($candle, 'long', $this->stopLoss);
        
        // Speichere Position
        $this->currentPosition = [
            'type' => 'long',
            'entry_price' => $price,
            'position_size' => $positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        
        // Inkrementiere die Gesamtzahl der Trades
        $this->totalTrades++;
        
        // Füge Trade zur Liste hinzu
        $trade = [
            'type' => 'long',
            'entry_time' => $candle['timestamp'],
            'entry_price' => $price,
            'position_size' => $positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        $this->trades[] = $trade;
        
        // Speichere Trade in der Datenbank
        $query = "INSERT INTO backtest_trades 
                 (run_id, type, entry_time, entry_price, position_size, stop_loss, take_profit) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $type = 'long';
        $stmt->bind_param("isiiddd", 
            $this->runId, 
            $type,
            $trade['entry_time'],
            $trade['entry_price'],
            $trade['position_size'],
            $trade['stop_loss'],
            $trade['take_profit']
        );
        $stmt->execute();
        
        echo sprintf(
            "Long Position eröffnet - Preis: %.2f, Size: %.2f, Stop Loss: %.2f, Take Profit: %.2f\n",
            $price,
            $positionSize,
            $this->stopLoss,
            $this->takeProfit
        );
    }
    
    private function enterShort($candle) {
        $price = $candle['close'];
        
        // Verwende nur 10% der Balance für jeden Trade
        $positionSize = $this->calculatePositionSize($candle);
        
        // Berechne Stop-Loss und Take-Profit
        $this->stopLoss = $this->calculateStopLoss($candle, 'short');
        $this->takeProfit = $this->calculateTakeProfit($candle, 'short', $this->stopLoss);
        
        // Speichere Position
        $this->currentPosition = [
            'type' => 'short',
            'entry_price' => $price,
            'position_size' => $positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        
        // Inkrementiere die Gesamtzahl der Trades
        $this->totalTrades++;
        
        // Füge Trade zur Liste hinzu
        $trade = [
            'type' => 'short',
            'entry_time' => $candle['timestamp'],
            'entry_price' => $price,
            'position_size' => $positionSize,
            'stop_loss' => $this->stopLoss,
            'take_profit' => $this->takeProfit
        ];
        $this->trades[] = $trade;
        
        // Speichere Trade in der Datenbank
        $query = "INSERT INTO backtest_trades 
                 (run_id, type, entry_time, entry_price, position_size, stop_loss, take_profit) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $type = 'short';
        $stmt->bind_param("isiiddd", 
            $this->runId, 
            $type,
            $trade['entry_time'],
            $trade['entry_price'],
            $trade['position_size'],
            $trade['stop_loss'],
            $trade['take_profit']
        );
        $stmt->execute();
        
        echo sprintf(
            "Short Position eröffnet - Preis: %.2f, Size: %.2f, Stop Loss: %.2f, Take Profit: %.2f\n",
            $price,
            $positionSize,
            $this->stopLoss,
            $this->takeProfit
        );
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
    
    private function calculateSMA($period) {
        $prices = array_column($this->marketData, 'close');
        return array_sum(array_slice($prices, -$period)) / $period;
    }
    
    private function calculateEMA($data, $period) {
        if (empty($data)) {
            return 0;
        }
        
        $multiplier = 2 / ($period + 1);
        $ema = array_sum(array_slice($data, 0, $period)) / $period;
        
        for ($i = $period; $i < count($data); $i++) {
            $ema = ($data[$i] - $ema) * $multiplier + $ema;
        }
        
        return $ema;
    }
    
    /**
     * Gibt die Backtest-Metriken zurück
     * @return array
     */
    public function getMetrics() {
        return $this->metrics;
    }
    
    private function printBacktestResults() {
        echo "\nBacktest-Ergebnisse:\n";
        echo "Gesamtanzahl Trades: " . count($this->trades) . "\n";
        echo "Gewinnende Trades: " . $this->winningTrades . " (" . 
             round(($this->winningTrades / count($this->trades)) * 100, 2) . "%)\n";
        echo "Verlierende Trades: " . $this->losingTrades . " (" . 
             round(($this->losingTrades / count($this->trades)) * 100, 2) . "%)\n";
        
        $profitFactor = $this->totalLoss != 0 ? abs($this->totalProfit / $this->totalLoss) : 0;
        echo "Profit Faktor: " . number_format($profitFactor, 2) . "\n";
        
        $netProfit = $this->totalProfit - abs($this->totalLoss);
        echo "Netto Gewinn: " . number_format($netProfit, 2) . " USDT\n";
        echo "Maximaler Drawdown: " . number_format($this->maxDrawdown, 2) . "%\n\n";
        
        echo "Einzelne Trades:\n";
        foreach ($this->trades as $trade) {
            $pnl = $trade['profit_loss'];
            $result = $pnl > 0 ? "GEWINN" : ($pnl < 0 ? "VERLUST" : "NEUTRAL");
            echo date('Y-m-d H:i', $trade['entry_time']) . " - " . 
                 $trade['type'] . " - " . 
                 "Entry: " . $trade['entry_price'] . " - " . 
                 "Exit: " . $trade['exit_price'] . " - " . 
                 "PnL: " . number_format($pnl, 2) . " USDT - " . 
                 $result . " - " . 
                 "Grund: " . $trade['exit_reason'] . "\n";
        }
    }
}
