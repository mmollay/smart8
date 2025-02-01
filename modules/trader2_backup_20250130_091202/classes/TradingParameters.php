<?php

class TradingParameters {
    private $db;
    private static $instance = null;
    private $parameters = [];

    private function __construct($db) {
        $this->db = $db;
        $this->loadParameters();
    }

    public static function getInstance($db) {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    private function loadParameters() {
        $query = "SELECT parameter_name, parameter_value FROM trading_parameters WHERE is_active = 1";
        $result = $this->db->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->parameters[$row['parameter_name']] = $row['parameter_value'];
            }
        }
    }

    public function get($parameterName) {
        return $this->parameters[$parameterName] ?? null;
    }

    public function calculateTakeProfit($price, $side) {
        $multiplier = $side === 'buy' 
            ? (1 + ($this->get('tp_percentage_long') / 100))
            : (1 - ($this->get('tp_percentage_short') / 100));
        return round($price * $multiplier, 2);
    }

    public function calculateStopLoss($price, $side) {
        $multiplier = $side === 'buy'
            ? (1 - ($this->get('sl_percentage_long') / 100))
            : (1 + ($this->get('sl_percentage_short') / 100));
        return round($price * $multiplier, 2);
    }

    public function validateTradeSize($size) {
        $minSize = $this->get('min_trade_size');
        $maxSize = $this->get('max_trade_size');
        return $size >= $minSize && $size <= $maxSize;
    }

    public function getDefaultValues() {
        return [
            'leverage' => $this->get('default_leverage'),
            'size' => $this->get('default_trade_size')
        ];
    }

    public function validateDailyTrading($userId) {
        // Überprüfen der täglichen Handelslimits
        $maxDailyTrades = $this->get('max_daily_trades');
        $maxDailyLoss = $this->get('max_daily_loss');

        // Anzahl der Trades heute
        $query = "SELECT COUNT(*) as trade_count FROM trades 
                 WHERE user_id = ? AND DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['trade_count'] >= $maxDailyTrades) {
            return ['valid' => false, 'message' => 'Maximale Anzahl täglicher Trades erreicht'];
        }

        // Täglicher Verlust berechnen
        $query = "SELECT SUM(pnl) as daily_pnl FROM trades 
                 WHERE user_id = ? AND DATE(closed_at) = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['daily_pnl'] < 0) {
            $dailyLossPercent = abs($result['daily_pnl']);
            if ($dailyLossPercent >= $maxDailyLoss) {
                return ['valid' => false, 'message' => 'Maximaler täglicher Verlust erreicht'];
            }
        }

        return ['valid' => true];
    }
}
