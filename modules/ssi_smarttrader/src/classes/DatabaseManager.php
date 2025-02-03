<?php
/**
 * DatabaseManager Klasse
 * Verwaltet alle Datenbankoperationen
 */
class DatabaseManager {
    private static $instance = null;
    private $db = null;

    private function __construct() {
        try {
            $this->db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $this->logError('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
            throw new Exception('Datenbankverbindung fehlgeschlagen');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Speichert einen neuen Trade
     */
    public function saveTrade($tradeData) {
        try {
            $sql = "INSERT INTO trades (
                symbol, position_type, entry_price, position_size, leverage,
                take_profit, stop_loss, order_id
            ) VALUES (
                :symbol, :position_type, :entry_price, :position_size, :leverage,
                :take_profit, :stop_loss, :order_id
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':symbol' => $tradeData['symbol'],
                ':position_type' => $tradeData['position_type'],
                ':entry_price' => $tradeData['entry_price'],
                ':position_size' => $tradeData['position_size'],
                ':leverage' => $tradeData['leverage'],
                ':take_profit' => $tradeData['take_profit'],
                ':stop_loss' => $tradeData['stop_loss'],
                ':order_id' => $tradeData['order_id']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logError('Fehler beim Speichern des Trades: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aktualisiert einen Trade
     */
    public function updateTrade($tradeId, $updateData) {
        try {
            $sets = [];
            $params = [':id' => $tradeId];

            foreach ($updateData as $key => $value) {
                $sets[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            $sql = "UPDATE trades SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError('Fehler beim Aktualisieren des Trades: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Speichert technische Indikatoren
     */
    public function saveIndicators($indicatorData) {
        try {
            $sql = "INSERT INTO technical_indicators (
                symbol, adx, plus_di, minus_di, atr, atr_percent,
                roc, volume, keltner_upper, keltner_middle, keltner_lower
            ) VALUES (
                :symbol, :adx, :plus_di, :minus_di, :atr, :atr_percent,
                :roc, :volume, :keltner_upper, :keltner_middle, :keltner_lower
            )";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':symbol' => $indicatorData['symbol'],
                ':adx' => $indicatorData['adx'],
                ':plus_di' => $indicatorData['plusDI'],
                ':minus_di' => $indicatorData['minusDI'],
                ':atr' => $indicatorData['atr'],
                ':atr_percent' => $indicatorData['atrPercent'],
                ':roc' => $indicatorData['roc'],
                ':volume' => $indicatorData['volume'],
                ':keltner_upper' => $indicatorData['keltner']['upper'],
                ':keltner_middle' => $indicatorData['keltner']['middle'],
                ':keltner_lower' => $indicatorData['keltner']['lower']
            ]);
        } catch (PDOException $e) {
            $this->logError('Fehler beim Speichern der Indikatoren: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Speichert Trade-Signale
     */
    public function saveTradeSignals($tradeId, $signals) {
        try {
            $sql = "INSERT INTO trade_signals (trade_id, signal_type, signal_value)
                   VALUES (:trade_id, :signal_type, :signal_value)";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($signals as $type => $typeSignals) {
                foreach ($typeSignals as $signal) {
                    $stmt->execute([
                        ':trade_id' => $tradeId,
                        ':signal_type' => $type,
                        ':signal_value' => $signal[1]
                    ]);
                }
            }
            return true;
        } catch (PDOException $e) {
            $this->logError('Fehler beim Speichern der Signale: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aktualisiert Performance-Metriken
     */
    public function updatePerformanceMetrics($symbol) {
        try {
            $date = date('Y-m-d');
            
            // Berechne Metriken
            $metrics = $this->calculatePerformanceMetrics($symbol);
            
            // Aktualisiere oder erstelle Eintrag
            $sql = "INSERT INTO performance_metrics (
                date, symbol, total_trades, winning_trades, losing_trades,
                profit_factor, win_rate, avg_win, avg_loss, max_drawdown,
                sharpe_ratio, total_profit_loss
            ) VALUES (
                :date, :symbol, :total_trades, :winning_trades, :losing_trades,
                :profit_factor, :win_rate, :avg_win, :avg_loss, :max_drawdown,
                :sharpe_ratio, :total_profit_loss
            ) ON DUPLICATE KEY UPDATE
                total_trades = VALUES(total_trades),
                winning_trades = VALUES(winning_trades),
                losing_trades = VALUES(losing_trades),
                profit_factor = VALUES(profit_factor),
                win_rate = VALUES(win_rate),
                avg_win = VALUES(avg_win),
                avg_loss = VALUES(avg_loss),
                max_drawdown = VALUES(max_drawdown),
                sharpe_ratio = VALUES(sharpe_ratio),
                total_profit_loss = VALUES(total_profit_loss)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute(array_merge(['date' => $date, 'symbol' => $symbol], $metrics));
        } catch (PDOException $e) {
            $this->logError('Fehler beim Aktualisieren der Performance-Metriken: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Berechnet Performance-Metriken
     */
    private function calculatePerformanceMetrics($symbol) {
        $sql = "SELECT 
                COUNT(*) as total_trades,
                SUM(CASE WHEN profit_loss > 0 THEN 1 ELSE 0 END) as winning_trades,
                SUM(CASE WHEN profit_loss < 0 THEN 1 ELSE 0 END) as losing_trades,
                AVG(CASE WHEN profit_loss > 0 THEN profit_loss ELSE NULL END) as avg_win,
                AVG(CASE WHEN profit_loss < 0 THEN ABS(profit_loss) ELSE NULL END) as avg_loss,
                SUM(profit_loss) as total_profit_loss
            FROM trades 
            WHERE symbol = :symbol 
            AND status = 'closed'
            AND exit_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':symbol' => $symbol]);
        $result = $stmt->fetch();

        // Berechne zusätzliche Metriken
        $winRate = $result['total_trades'] > 0 ? 
            ($result['winning_trades'] / $result['total_trades']) * 100 : 0;

        $profitFactor = $result['avg_loss'] > 0 ? 
            ($result['avg_win'] * $result['winning_trades']) / 
            ($result['avg_loss'] * $result['losing_trades']) : 0;

        // Berechne Drawdown
        $maxDrawdown = $this->calculateMaxDrawdown($symbol);

        // Berechne Sharpe Ratio
        $sharpeRatio = $this->calculateSharpeRatio($symbol);

        return [
            'total_trades' => $result['total_trades'],
            'winning_trades' => $result['winning_trades'],
            'losing_trades' => $result['losing_trades'],
            'profit_factor' => $profitFactor,
            'win_rate' => $winRate,
            'avg_win' => $result['avg_win'],
            'avg_loss' => $result['avg_loss'],
            'max_drawdown' => $maxDrawdown,
            'sharpe_ratio' => $sharpeRatio,
            'total_profit_loss' => $result['total_profit_loss']
        ];
    }

    /**
     * Berechnet den maximalen Drawdown
     */
    private function calculateMaxDrawdown($symbol) {
        $sql = "SELECT profit_loss, entry_time 
                FROM trades 
                WHERE symbol = :symbol 
                AND status = 'closed'
                AND exit_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY entry_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':symbol' => $symbol]);
        $trades = $stmt->fetchAll();

        $peak = 0;
        $maxDrawdown = 0;
        $currentValue = 0;

        foreach ($trades as $trade) {
            $currentValue += $trade['profit_loss'];
            if ($currentValue > $peak) {
                $peak = $currentValue;
            }
            $drawdown = ($peak - $currentValue) / $peak * 100;
            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }

        return $maxDrawdown;
    }

    /**
     * Berechnet die Sharpe Ratio
     */
    private function calculateSharpeRatio($symbol) {
        $sql = "SELECT profit_loss 
                FROM trades 
                WHERE symbol = :symbol 
                AND status = 'closed'
                AND exit_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':symbol' => $symbol]);
        $trades = $stmt->fetchAll();

        if (count($trades) < 2) {
            return 0;
        }

        $returns = array_column($trades, 'profit_loss');
        $avgReturn = array_sum($returns) / count($returns);
        
        $variance = 0;
        foreach ($returns as $return) {
            $variance += pow($return - $avgReturn, 2);
        }
        $stdDev = sqrt($variance / (count($returns) - 1));

        // Angenommener risikofreier Zinssatz von 2%
        $riskFreeRate = 0.02;
        
        return $stdDev > 0 ? 
            (($avgReturn - $riskFreeRate) / $stdDev) * sqrt(365) : 0;
    }

    /**
     * Loggt einen Systemfehler
     */
    private function logError($message, $details = null) {
        try {
            $sql = "INSERT INTO system_log (log_level, component, message, details)
                   VALUES (:level, :component, :message, :details)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':level' => 'error',
                ':component' => 'DatabaseManager',
                ':message' => $message,
                ':details' => $details ? json_encode($details) : null
            ]);
        } catch (PDOException $e) {
            error_log('Fehler beim Loggen: ' . $e->getMessage());
        }
    }
}
