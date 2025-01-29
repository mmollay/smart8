<?php
// classes/MarketAnalysis.php

class MarketAnalysis
{
    private $db;
    private $trading;
    private $symbol;

    public function __construct($db, $trading, $symbol = 'ETHUSDT_UMCBL')
    {
        $this->db = $db;
        $this->trading = $trading;
        $this->symbol = $symbol;
    }

    public function analyze()
    {
        try {
            // Aktuelle Marktdaten holen
            $klines = $this->trading->getKlines($this->symbol);
            $currentPrice = $klines['currentPrice'];

            // Technische Indikatoren berechnen
            $prices = $this->getHistoricalPrices();
            $rsi = $this->calculateRSI($prices);
            $ema20 = $this->calculateEMA($prices, 20);
            $ema50 = $this->calculateEMA($prices, 50);

            // Score und Signale berechnen
            $score = 50; // Neutraler Ausgangspunkt
            $reasoning = [];

            // RSI Analyse
            if ($rsi < 30) {
                $score += 20;
                $reasoning[] = "RSI zeigt überverkaufte Bedingungen ($rsi)";
            } elseif ($rsi > 70) {
                $score -= 20;
                $reasoning[] = "RSI zeigt überkaufte Bedingungen ($rsi)";
            }

            // EMA Analyse
            if ($ema20 > $ema50) {
                $score += 15;
                $reasoning[] = "EMA20 über EMA50 deutet auf Aufwärtstrend";
            } else {
                $score -= 15;
                $reasoning[] = "EMA20 unter EMA50 deutet auf Abwärtstrend";
            }

            // Preis zu EMAs
            if ($currentPrice > $ema20 && $currentPrice > $ema50) {
                $score += 15;
                $reasoning[] = "Preis über beiden EMAs zeigt Stärke";
            } elseif ($currentPrice < $ema20 && $currentPrice < $ema50) {
                $score -= 15;
                $reasoning[] = "Preis unter beiden EMAs zeigt Schwäche";
            }

            // Trading Signal generieren
            $action = $score >= 50 ? 'buy' : 'sell';
            $confidence = abs($score - 50);

            return [
                'action' => $action,
                'confidence' => $confidence,
                'entry_price' => $currentPrice,
                'tp_price' => $action === 'buy' ? $currentPrice * 1.015 : $currentPrice * 0.985,
                'sl_price' => $action === 'buy' ? $currentPrice * 0.9925 : $currentPrice * 1.0075,
                'analysis' => [
                    'rsi' => $rsi,
                    'ema20' => $ema20,
                    'ema50' => $ema50
                ],
                'reasoning' => $reasoning
            ];

        } catch (Exception $e) {
            error_log("Error in analyze: " . $e->getMessage());
            throw $e;
        }
    }

    private function getHistoricalPrices($limit = 100)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT price 
                FROM market_data 
                WHERE symbol = ? 
                ORDER BY timestamp DESC 
                LIMIT ?
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param("si", $this->symbol, $limit);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result();

            $prices = [];
            while ($row = $result->fetch_assoc()) {
                $prices[] = $row['price'];
            }
            return array_reverse($prices);

        } catch (Exception $e) {
            error_log("Error getting historical prices: " . $e->getMessage());
            return [];
        }
    }

    private function calculateRSI($prices, $period = 14)
    {
        if (count($prices) < $period + 1) {
            return 50; // Neutraler Wert wenn nicht genug Daten
        }

        $gains = [];
        $losses = [];

        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            $gains[] = max(0, $change);
            $losses[] = max(0, -$change);
        }

        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        if ($avgLoss == 0) {
            return 100;
        }

        return 100 - (100 / (1 + ($avgGain / $avgLoss)));
    }

    private function calculateEMA($prices, $period = 20)
    {
        if (count($prices) < $period) {
            return end($prices);
        }

        $multiplier = 2 / ($period + 1);
        $ema = array_sum(array_slice($prices, 0, $period)) / $period;

        foreach (array_slice($prices, $period) as $price) {
            $ema = ($price - $ema) * $multiplier + $ema;
        }

        return $ema;
    }
}