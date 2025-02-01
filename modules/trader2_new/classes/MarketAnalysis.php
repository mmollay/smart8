<?php
class MarketAnalysis {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function analyzeMarket($symbol, $period = 14) {
        try {
            // Hole die neuesten Marktdaten
            $query = "SELECT price, volume, timestamp 
                     FROM market_data 
                     WHERE symbol = ? 
                     ORDER BY timestamp DESC 
                     LIMIT ?";
                     
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $symbol, $period);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $prices = [];
            $volumes = [];
            while ($row = $result->fetch_assoc()) {
                $prices[] = $row['price'];
                $volumes[] = $row['volume'];
            }
            
            if (count($prices) < $period) {
                throw new Exception("Nicht genügend Daten für Analyse");
            }
            
            // Berechne technische Indikatoren
            $rsi = $this->calculateRSI($prices);
            $sma20 = $this->calculateSMA($prices, 20);
            $volumeAnalysis = $this->analyzeVolume($volumes);
            $trend = $this->determineTrend($prices, $sma20);
            
            return [
                'rsi' => $rsi,
                'sma20' => $sma20,
                'volume_strength' => $volumeAnalysis['strength'],
                'volume_trend' => $volumeAnalysis['trend'],
                'trend' => $trend,
                'timestamp' => time()
            ];
            
        } catch (Exception $e) {
            logError("Marktanalyse Fehler", [
                'error' => $e->getMessage(),
                'symbol' => $symbol
            ]);
            throw $e;
        }
    }
    
    private function calculateRSI($prices, $period = 14) {
        if (count($prices) < $period + 1) {
            return null;
        }
        
        $gains = [];
        $losses = [];
        
        // Berechne Gewinne und Verluste
        for ($i = 1; $i < count($prices); $i++) {
            $difference = $prices[$i] - $prices[$i - 1];
            if ($difference >= 0) {
                $gains[] = $difference;
                $losses[] = 0;
            } else {
                $gains[] = 0;
                $losses[] = abs($difference);
            }
        }
        
        // Berechne durchschnittliche Gewinne und Verluste
        $avgGain = array_sum(array_slice($gains, -$period)) / $period;
        $avgLoss = array_sum(array_slice($losses, -$period)) / $period;
        
        // Berechne RSI
        if ($avgLoss == 0) {
            return 100;
        }
        
        $rs = $avgGain / $avgLoss;
        return 100 - (100 / (1 + $rs));
    }
    
    private function calculateSMA($prices, $period = 20) {
        if (count($prices) < $period) {
            return null;
        }
        
        $relevantPrices = array_slice($prices, 0, $period);
        return array_sum($relevantPrices) / $period;
    }
    
    private function analyzeVolume($volumes) {
        $avgVolume = array_sum($volumes) / count($volumes);
        $lastVolume = $volumes[0];
        
        return [
            'strength' => $lastVolume / $avgVolume,
            'trend' => $lastVolume > $avgVolume ? 'steigend' : 'fallend'
        ];
    }
    
    private function determineTrend($prices, $sma) {
        if ($sma === null) {
            return 'unbekannt';
        }
        
        $currentPrice = $prices[0];
        $priceChange = ($currentPrice - $prices[count($prices)-1]) / $prices[count($prices)-1] * 100;
        
        if ($currentPrice > $sma && $priceChange > 0) {
            return 'stark steigend';
        } elseif ($currentPrice > $sma) {
            return 'leicht steigend';
        } elseif ($currentPrice < $sma && $priceChange < 0) {
            return 'stark fallend';
        } elseif ($currentPrice < $sma) {
            return 'leicht fallend';
        }
        
        return 'seitwärts';
    }
    
    public function generateSignal($analysis, $modelParams) {
        $signal = 'hold';
        $confidence = 0;
        $reasons = [];
        
        // RSI-basierte Signale
        if ($analysis['rsi'] <= 30) {
            $signal = 'buy';
            $confidence += 30;
            $reasons[] = "RSI überkauft (${analysis['rsi']})";
        } elseif ($analysis['rsi'] >= 70) {
            $signal = 'sell';
            $confidence += 30;
            $reasons[] = "RSI überverkauft (${analysis['rsi']})";
        }
        
        // Trend-basierte Signale
        if ($analysis['trend'] === 'stark steigend') {
            if ($signal !== 'sell') {
                $signal = 'buy';
                $confidence += 40;
                $reasons[] = "Starker Aufwärtstrend";
            }
        } elseif ($analysis['trend'] === 'stark fallend') {
            if ($signal !== 'buy') {
                $signal = 'sell';
                $confidence += 40;
                $reasons[] = "Starker Abwärtstrend";
            }
        }
        
        // Volumen-Bestätigung
        if ($analysis['volume_strength'] > 1.5) {
            $confidence += 20;
            $reasons[] = "Hohes Handelsvolumen";
        }
        
        // Modell-spezifische Anpassungen
        if (isset($modelParams['risk_level'])) {
            switch ($modelParams['risk_level']) {
                case 'konservativ':
                    $confidence *= 0.8;
                    break;
                case 'aggressiv':
                    $confidence *= 1.2;
                    break;
            }
        }
        
        return [
            'action' => $signal,
            'confidence' => min(100, $confidence),
            'reasons' => $reasons
        ];
    }
}
