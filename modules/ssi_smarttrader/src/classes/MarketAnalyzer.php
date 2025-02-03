<?php
/**
 * MarketAnalyzer Klasse
 * Implementiert die technische Analyse und Handelssignale
 */

// Handelskonstanten
if (!defined('MIN_ADX')) {
    define('MIN_ADX', 25);
    define('MAX_ADX', 50);
    define('MIN_DI_DIFF', 5);
    define('MAX_ATR_PERCENT', 2);
    define('MIN_ROC', 0.5);
    define('MAX_ROC', 10);
    define('MIN_VOLUME', 0.3);
}

class MarketAnalyzer {
    private $api;
    private $klines;
    private $currentPrice;

    public function __construct() {
        $this->api = new BitgetAPI();
    }

    /**
     * Führt die Marktanalyse durch
     */
    public function analyze($symbol) {
        try {
            // Hole Kerzendaten
            $response = $this->api->getKlines($symbol, '5m', 100);
            if (empty($response)) {
                throw new Exception('Keine Kerzendaten verfügbar');
            }

            // Formatiere Klines-Daten
            $formattedKlines = [];
            foreach ($response as $kline) {
                $formattedKlines[] = [
                    'timestamp' => $kline[0],
                    'open' => floatval($kline[1]),
                    'high' => floatval($kline[2]),
                    'low' => floatval($kline[3]),
                    'close' => floatval($kline[4]),
                    'volume' => floatval($kline[5]),
                    'quoteVolume' => floatval($kline[6])
                ];
            }
            $this->klines = $formattedKlines;

            // Hole aktuellen Preis
            $priceData = $this->api->getSymbolPrice($symbol);
            if (!isset($priceData['data']['last'])) {
                throw new Exception('Aktueller Preis nicht verfügbar');
            }
            $this->currentPrice = floatval($priceData['data']['last']);

            // Extrahiere Daten für Indikatoren
            $highs = array_column($this->klines, 'high');
            $lows = array_column($this->klines, 'low');
            $closes = array_column($this->klines, 'close');
            $volumes = array_column($this->klines, 'volume');
            $currentPrice = $this->currentPrice;
            
            // Berechne technische Indikatoren
            $adx = $this->calculateADX();
            $plusDI = $this->calculatePlusDI();
            $minusDI = $this->calculateMinusDI();
            $atr = $this->calculateATR($highs, $lows, $closes);
            $roc = $this->calculateROC($closes);
            $volume = array_sum(array_slice($volumes, -10)) / 10;  // 10-Perioden Durchschnitt
            $keltner = $this->calculateKeltnerChannel($highs, $lows, $closes);
            
            // Analysiere Signale
            $longSignals = $this->analyzeLongSignals($adx, $atr, $roc, $volume, $keltner, $currentPrice);
            $shortSignals = $this->analyzeShortSignals($adx, $atr, $roc, $volume, $keltner, $currentPrice);
            
            // Berechne Scores
            $longScore = $this->calculateScore($longSignals);
            $shortScore = $this->calculateScore($shortSignals);
            
            return [
                'currentPrice' => $currentPrice,
                'recommendation' => $this->getRecommendation($longScore, $shortScore),
                'indicators' => [
                    'adx' => round($adx, 2),
                    'plusDI' => round($plusDI, 2),
                    'minusDI' => round($minusDI, 2),
                    'atr' => round($atr, 2),
                    'atrPercent' => round(($atr / $currentPrice) * 100, 2),
                    'roc' => round($roc, 2),
                    'volume' => round($volume, 2),
                    'keltner' => [
                        'upper' => round($keltner['upper'], 2),
                        'middle' => round($keltner['middle'], 2),
                        'lower' => round($keltner['lower'], 2)
                    ]
                ],
                'signals' => [
                    'long' => $longSignals,
                    'short' => $shortSignals
                ],
                'scores' => [
                    'long' => $longScore,
                    'short' => $shortScore
                ]
            ];
        } catch (Exception $e) {
            throw new Exception('Fehler bei der Marktanalyse: ' . $e->getMessage());
        }
    }

    /**
     * Berechnet den ADX (Average Directional Index)
     */
    private function calculateADX() {
        $periods = 14;
        $data = array_reverse($this->klines);
        
        $trueRanges = [];
        $plusDMs = [];
        $minusDMs = [];
        
        for ($i = 1; $i < count($data); $i++) {
            $high = $data[$i]['high'];
            $low = $data[$i]['low'];
            $prevHigh = $data[$i-1]['high'];
            $prevLow = $data[$i-1]['low'];
            $prevClose = $data[$i-1]['close'];
            
            // True Range
            $tr1 = abs($high - $low);
            $tr2 = abs($high - $prevClose);
            $tr3 = abs($low - $prevClose);
            $trueRanges[] = max($tr1, $tr2, $tr3);
            
            // Directional Movement
            $upMove = $high - $prevHigh;
            $downMove = $prevLow - $low;
            
            if ($upMove > $downMove && $upMove > 0) {
                $plusDMs[] = $upMove;
            } else {
                $plusDMs[] = 0;
            }
            
            if ($downMove > $upMove && $downMove > 0) {
                $minusDMs[] = $downMove;
            } else {
                $minusDMs[] = 0;
            }
        }
        
        // Berechne Durchschnitte
        $atr = array_sum(array_slice($trueRanges, 0, $periods)) / $periods;
        $plusDI = (array_sum(array_slice($plusDMs, 0, $periods)) / $atr) * 100 / $periods;
        $minusDI = (array_sum(array_slice($minusDMs, 0, $periods)) / $atr) * 100 / $periods;
        
        // Berechne ADX
        $dx = abs($plusDI - $minusDI) / ($plusDI + $minusDI) * 100;
        return $dx;
    }

    /**
     * Berechnet +DI
     */
    private function calculatePlusDI() {
        $periods = 14;
        $data = array_reverse($this->klines);
        $plusDMs = [];
        
        for ($i = 1; $i < count($data); $i++) {
            $high = $data[$i]['high'];
            $prevHigh = $data[$i-1]['high'];
            $upMove = $high - $prevHigh;
            
            if ($upMove > 0) {
                $plusDMs[] = $upMove;
            } else {
                $plusDMs[] = 0;
            }
        }
        
        return array_sum(array_slice($plusDMs, 0, $periods)) / $periods;
    }

    /**
     * Berechnet -DI
     */
    private function calculateMinusDI() {
        $periods = 14;
        $data = array_reverse($this->klines);
        $minusDMs = [];
        
        for ($i = 1; $i < count($data); $i++) {
            $low = $data[$i]['low'];
            $prevLow = $data[$i-1]['low'];
            $downMove = $prevLow - $low;
            
            if ($downMove > 0) {
                $minusDMs[] = $downMove;
            } else {
                $minusDMs[] = 0;
            }
        }
        
        return array_sum(array_slice($minusDMs, 0, $periods)) / $periods;
    }

    /**
     * Berechnet ATR (Average True Range)
     */
    private function calculateATR($highs, $lows, $closes) {
        $periods = 14;
        $trueRanges = [];
        
        for ($i = 1; $i < count($closes); $i++) {
            $high = $highs[$i];
            $low = $lows[$i];
            $prevClose = $closes[$i-1];
            
            $tr1 = abs($high - $low);
            $tr2 = abs($high - $prevClose);
            $tr3 = abs($low - $prevClose);
            $trueRanges[] = max($tr1, $tr2, $tr3);
        }
        
        return array_sum(array_slice($trueRanges, 0, $periods)) / $periods;
    }

    /**
     * Berechnet ATR als Prozentsatz vom Preis
     */
    private function calculateATRPercent($atr, $currentPrice) {
        return ($atr / $currentPrice) * 100;
    }

    /**
     * Berechnet ROC (Rate of Change)
     */
    private function calculateROC($closes) {
        $periods = 14;
        
        if (count($closes) < $periods) {
            return 0;
        }
        
        $currentClose = $closes[0];
        $prevClose = $closes[$periods-1];
        
        return (($currentClose - $prevClose) / $prevClose) * 100;
    }

    /**
     * Berechnet durchschnittliches Volumen
     */
    private function calculateVolume($volumes) {
        $periods = 20;
        
        return array_sum(array_slice($volumes, -10)) / 10;  // 10-Perioden Durchschnitt
    }

    /**
     * Berechnet Keltner Channels
     */
    private function calculateKeltnerChannel($highs, $lows, $closes) {
        $periods = 20;
        $multiplier = 2;
        
        // Berechne EMA
        $ema = array_sum(array_slice($closes, 0, $periods)) / $periods;
        $atr = $this->calculateATR($highs, $lows, $closes);
        
        return [
            'upper' => $ema + ($multiplier * $atr),
            'middle' => $ema,
            'lower' => $ema - ($multiplier * $atr)
        ];
    }

    /**
     * Analysiert Long Signale
     */
    private function analyzeLongSignals($adx, $atr, $roc, $volume, $keltner, $currentPrice) {
        $signals = [];
        
        // ADX Signal
        if ($adx >= MIN_ADX && $adx <= MAX_ADX) {
            $signals[] = ['positive', 'ADX zeigt starken Aufwärtstrend'];
        }
        
        // Volatilitäts-Signal
        if ($atr <= MAX_ATR_PERCENT * $currentPrice) {
            $signals[] = ['positive', 'Moderate Volatilität für Long'];
        } else {
            $signals[] = ['negative', 'Zu hohe Volatilität'];
        }
        
        // ROC Signal
        if ($roc >= MIN_ROC && $roc <= MAX_ROC) {
            $signals[] = ['positive', 'Positiver Momentum für Long'];
        }
        
        // Volumen Signal
        if ($volume >= MIN_VOLUME) {
            $signals[] = ['positive', 'Ausreichendes Handelsvolumen'];
        } else {
            $signals[] = ['negative', 'Zu geringes Volumen'];
        }
        
        // Keltner Channel Signal
        if ($currentPrice > $keltner['middle']) {
            $signals[] = ['positive', 'Preis über Keltner Mittelband'];
        } else {
            $signals[] = ['negative', 'Preis unter Keltner Mittelband'];
        }
        
        return $signals;
    }

    /**
     * Analysiert Short Signale
     */
    private function analyzeShortSignals($adx, $atr, $roc, $volume, $keltner, $currentPrice) {
        $signals = [];
        
        // ADX Signal
        if ($adx >= MIN_ADX && $adx <= MAX_ADX) {
            $signals[] = ['positive', 'ADX zeigt starken Abwärtstrend'];
        }
        
        // Volatilitäts-Signal
        if ($atr <= MAX_ATR_PERCENT * $currentPrice) {
            $signals[] = ['positive', 'Moderate Volatilität für Short'];
        } else {
            $signals[] = ['negative', 'Zu hohe Volatilität'];
        }
        
        // ROC Signal
        if ($roc <= -MIN_ROC && $roc >= -MAX_ROC) {
            $signals[] = ['positive', 'Negativer Momentum für Short'];
        }
        
        // Volumen Signal
        if ($volume >= MIN_VOLUME) {
            $signals[] = ['positive', 'Ausreichendes Handelsvolumen'];
        } else {
            $signals[] = ['negative', 'Zu geringes Volumen'];
        }
        
        // Keltner Channel Signal
        if ($currentPrice < $keltner['middle']) {
            $signals[] = ['positive', 'Preis unter Keltner Mittelband'];
        } else {
            $signals[] = ['negative', 'Preis über Keltner Mittelband'];
        }
        
        return $signals;
    }

    /**
     * Berechnet Score
     */
    private function calculateScore($signals) {
        $score = 0;
        
        foreach ($signals as $signal) {
            if ($signal[0] === 'positive') {
                $score += 20;
            }
        }
        
        return min($score, 100);
    }

    /**
     * Gibt Handelsempfehlung zurück
     */
    private function getRecommendation($longScore, $shortScore) {
        $minConfidence = 60;
        
        if ($longScore >= $minConfidence && $longScore > $shortScore) {
            return [
                'type' => 'long',
                'confidence' => $longScore
            ];
        }
        
        if ($shortScore >= $minConfidence && $shortScore > $longScore) {
            return [
                'type' => 'short',
                'confidence' => $shortScore
            ];
        }
        
        return [
            'type' => 'neutral',
            'confidence' => max($longScore, $shortScore)
        ];
    }
}
