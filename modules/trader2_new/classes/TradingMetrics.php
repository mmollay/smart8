<?php

class TradingMetrics {
    private $db;
    
    public function __construct(DatabaseHandler $db) {
        $this->db = $db;
    }
    
    public function getPerformanceMetrics() {
        // Hole die letzten 100 geschlossenen Trades
        $trades = $this->getClosedTrades(100);
        
        if (empty($trades)) {
            return [
                'profit_factor' => 0,
                'win_rate' => 0,
                'sharpe_ratio' => 0,
                'max_drawdown' => 0
            ];
        }
        
        // Berechne Profit Factor
        $totalProfit = 0;
        $totalLoss = 0;
        $wins = 0;
        $returns = [];
        $balance = 10000; // Anfangsbalance für Drawdown-Berechnung
        $maxBalance = $balance;
        $maxDrawdown = 0;
        
        foreach ($trades as $trade) {
            $pnl = $trade['realized_pnl'];
            
            if ($pnl > 0) {
                $totalProfit += $pnl;
                $wins++;
            } else {
                $totalLoss += abs($pnl);
            }
            
            // Für Sharpe Ratio
            $returns[] = $pnl / $balance;
            
            // Für Max Drawdown
            $balance += $pnl;
            $maxBalance = max($maxBalance, $balance);
            $currentDrawdown = ($maxBalance - $balance) / $maxBalance;
            $maxDrawdown = max($maxDrawdown, $currentDrawdown);
        }
        
        // Profit Factor
        $profitFactor = $totalLoss > 0 ? $totalProfit / $totalLoss : $totalProfit;
        
        // Win Rate
        $winRate = count($trades) > 0 ? $wins / count($trades) : 0;
        
        // Sharpe Ratio (annualisiert, angenommen tägliche Returns)
        $avgReturn = array_sum($returns) / count($returns);
        $stdDev = $this->calculateStdDev($returns);
        $sharpeRatio = $stdDev > 0 ? ($avgReturn / $stdDev) * sqrt(365) : 0;
        
        return [
            'profit_factor' => $profitFactor,
            'win_rate' => $winRate,
            'sharpe_ratio' => $sharpeRatio,
            'max_drawdown' => $maxDrawdown
        ];
    }
    
    private function getClosedTrades($limit = 100) {
        $query = "SELECT * FROM trades WHERE status = 'closed' ORDER BY close_time DESC LIMIT ?";
        return $this->db->query($query, [$limit]);
    }
    
    private function calculateStdDev($array) {
        $n = count($array);
        if ($n < 2) {
            return 0;
        }
        
        $mean = array_sum($array) / $n;
        $squaredDiffs = array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $array);
        
        return sqrt(array_sum($squaredDiffs) / ($n - 1));
    }
}
