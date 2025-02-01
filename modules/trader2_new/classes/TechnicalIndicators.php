<?php

class TechnicalIndicators {
    public function __construct() {
    }
    
    public function getIndicatorSignals($data) {
        if (empty($data)) {
            return [];
        }
        
        // Berechne RSI
        $rsi = $this->calculateRSI($data);
        
        // Berechne MACD
        $macd = $this->calculateMACD($data);
        
        // Berechne Bollinger Bands
        $bollingerBands = $this->calculateBollingerBands($data);
        
        // Generiere Handelssignale basierend auf den Indikatoren
        $signals = [];
        
        $lastCandle = end($data);
        $currentPrice = $lastCandle['close'];
        
        // RSI Signale
        if ($rsi < 30) {
            $signals[] = ['signal' => 'buy', 'indicator' => 'RSI', 'value' => $rsi];
        } elseif ($rsi > 70) {
            $signals[] = ['signal' => 'sell', 'indicator' => 'RSI', 'value' => $rsi];
        }
        
        // MACD Signale
        if ($macd['histogram'] > 0 && $macd['histogram'] > $macd['signal']) {
            $signals[] = ['signal' => 'buy', 'indicator' => 'MACD', 'value' => $macd['histogram']];
        } elseif ($macd['histogram'] < 0 && $macd['histogram'] < $macd['signal']) {
            $signals[] = ['signal' => 'sell', 'indicator' => 'MACD', 'value' => $macd['histogram']];
        }
        
        // Bollinger Bands Signale
        if ($currentPrice <= $bollingerBands['lower']) {
            $signals[] = ['signal' => 'buy', 'indicator' => 'BB', 'value' => $currentPrice];
        } elseif ($currentPrice >= $bollingerBands['upper']) {
            $signals[] = ['signal' => 'sell', 'indicator' => 'BB', 'value' => $currentPrice];
        }
        
        return $signals;
    }
    
    public function calculateRSI($data, $period = 14, $currentTime = null) {
        if (empty($data)) {
            return null;
        }
        
        // Extrahiere die Schlusskurse
        $prices = array_map(function($candle) {
            return $candle['close'];
        }, $data);
        
        if (count($prices) < $period + 1) {
            return null;
        }
        
        $gains = 0;
        $losses = 0;
        
        // Berechne die durchschnittlichen Gewinne und Verluste
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i-1];
            if ($change >= 0) {
                $gains += $change;
            } else {
                $losses += abs($change);
            }
        }
        
        $avgGain = $gains / $period;
        $avgLoss = $losses / $period;
        
        // Verhindere Division durch Null
        if ($avgLoss == 0) {
            return 100;
        }
        
        $rs = $avgGain / $avgLoss;
        $rsi = 100 - (100 / (1 + $rs));
        
        // Debug-Ausgabe
        echo sprintf(
            "RSI Berechnung: Gains=%.2f, Losses=%.2f, AvgGain=%.2f, AvgLoss=%.2f, RS=%.2f, RSI=%.2f\n",
            $gains, $losses, $avgGain, $avgLoss, $rs, $rsi
        );
        
        return $rsi;
    }
    
    public function calculateMACD($data, $fastPeriod = 12, $slowPeriod = 26, $signalPeriod = 9, $currentTime = null) {
        if (empty($data)) {
            return null;
        }
        
        // Extrahiere die Schlusskurse
        $prices = array_map(function($candle) {
            return $candle['close'];
        }, $data);
        
        if (count($prices) < max($fastPeriod, $slowPeriod) + $signalPeriod) {
            return null;
        }
        
        // Berechne EMAs
        $fastEMA = $this->calculateEMA($prices, $fastPeriod);
        $slowEMA = $this->calculateEMA($prices, $slowPeriod);
        
        if ($fastEMA === null || $slowEMA === null) {
            return null;
        }
        
        // Berechne MACD-Linie
        $macdLine = $fastEMA - $slowEMA;
        
        // Berechne Signal-Linie (9-Perioden EMA der MACD-Linie)
        $macdHistory = [];
        for ($i = 0; $i < count($prices); $i++) {
            if ($i >= max($fastPeriod, $slowPeriod) - 1) {
                $tempPrices = array_slice($prices, 0, $i + 1);
                $tempFastEMA = $this->calculateEMA($tempPrices, $fastPeriod);
                $tempSlowEMA = $this->calculateEMA($tempPrices, $slowPeriod);
                
                if ($tempFastEMA !== null && $tempSlowEMA !== null) {
                    $macdHistory[] = $tempFastEMA - $tempSlowEMA;
                }
            }
        }
        
        $signalLine = $this->calculateEMA($macdHistory, $signalPeriod);
        
        if ($signalLine === null) {
            return null;
        }
        
        // Berechne Histogram
        $histogram = $macdLine - $signalLine;
        
        // Debug-Ausgabe
        echo sprintf(
            "MACD Berechnung: FastEMA=%.2f, SlowEMA=%.2f, MACD=%.2f, Signal=%.2f, Histogram=%.2f\n",
            $fastEMA, $slowEMA, $macdLine, $signalLine, $histogram
        );
        
        return [
            'macd' => $macdLine,
            'signal' => $signalLine,
            'histogram' => $histogram
        ];
    }
    
    private function calculateEMA($data, $period) {
        if (empty($data) || count($data) < $period) {
            return null;
        }
        
        // Berechne ersten SMA als Startpunkt
        $sma = array_sum(array_slice($data, 0, $period)) / $period;
        
        // EMA = Preis(t) * k + EMA(y) * (1-k)
        // wobei k = 2/(period + 1)
        $multiplier = 2 / ($period + 1);
        $ema = $sma;
        
        for ($i = $period; $i < count($data); $i++) {
            $ema = ($data[$i] * $multiplier) + ($ema * (1 - $multiplier));
        }
        
        return $ema;
    }
    
    public function calculateBollingerBands($data, $period = 20, $stdDev = 2) {
        if (count($data) < $period) {
            $price = end($data)['close'];
            return ['upper' => $price * 1.02, 'middle' => $price, 'lower' => $price * 0.98];
        }
        
        // Berechne SMA
        $prices = array_slice(array_column($data, 'close'), -$period);
        $sma = array_sum($prices) / $period;
        
        // Berechne Standardabweichung
        $variance = 0;
        foreach ($prices as $price) {
            $variance += pow($price - $sma, 2);
        }
        $stdDeviation = sqrt($variance / $period);
        
        return [
            'upper' => $sma + ($stdDeviation * $stdDev),
            'middle' => $sma,
            'lower' => $sma - ($stdDeviation * $stdDev)
        ];
    }
}
