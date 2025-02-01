<?php

class MarketData {
    private $db;
    private $apiKey = 'bg_cc89302322ccb5c2c3942f70dfbd8d2e';
    private $secretKey = '1c852a6f5c8d2d5a9cb5b9e83e02e1c9e7f8c8c6b6c3d4a6b1c8c2d3e4f5a6b7';
    private $passphrase = 'MCmaster23';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getHistoricalData($symbol, $interval, $limit = 100) {
        // Berechne Start- und Endzeit basierend auf Limit und Interval
        $endTime = time();
        $intervalSeconds = $this->intervalToSeconds($interval);
        $startTime = $endTime - ($intervalSeconds * $limit);
        
        // Hole Marktdaten
        $data = $this->getDataFromDatabase($symbol, $interval, $startTime, $endTime);
        
        if (empty($data)) {
            // Wenn keine Daten in der DB, hole sie von der API
            $data = $this->fetchDataFromAPI($symbol, $interval, $startTime, $endTime);
            
            if (!empty($data)) {
                // Speichere die Daten in der Datenbank
                $this->saveDataToDatabase($symbol, $interval, $data);
            }
        }
        
        if (!empty($data)) {
            // Berechne technische Indikatoren für jeden Datenpunkt
            foreach ($data as &$candle) {
                $indicators = $this->calculateIndicators($symbol, $candle['timestamp']);
                $candle = array_merge($candle, $indicators);
            }
        }
        
        return $data;
    }
    
    private function intervalToSeconds($interval) {
        $unit = substr($interval, -1);
        $value = (int)substr($interval, 0, -1);
        
        switch ($unit) {
            case 'm': return $value * 60;
            case 'h': return $value * 3600;
            case 'd': return $value * 86400;
            case 'w': return $value * 604800;
            default: return 900; // Standard: 15 Minuten
        }
    }
    
    private function calculateIndicators($symbol, $timestamp) {
        // Hole die letzten 14 Kerzen für die Berechnung
        $endTime = $timestamp;
        $startTime = $endTime - (14 * 86400); // 14 Tage zurück
        
        $query = "SELECT * FROM technical_analysis ta
                 JOIN market_data md ON ta.market_data_id = md.id
                 WHERE md.symbol = ? AND md.timestamp = ?";
                 
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $symbol, $timestamp);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'adx' => (float)$row['adx'],
                    'plus_di' => (float)$row['plus_di'],
                    'minus_di' => (float)$row['minus_di'],
                    'atr' => (float)$row['atr'],
                    'roc' => (float)$row['roc']
                ];
            }
            
            // Wenn keine Indikatoren in der DB, berechne sie
            $data = $this->getDataFromDatabase($symbol, '1d', $startTime, $endTime);
            if (empty($data)) {
                return [
                    'adx' => 0,
                    'plus_di' => 0,
                    'minus_di' => 0,
                    'atr' => 0,
                    'roc' => 0
                ];
            }
            
            // Berechne Indikatoren
            $adx = $this->calculateADX($data);
            $atr = $this->calculateATR($data);
            $roc = $this->calculateROC($data);
            
            // Speichere Indikatoren in der DB
            $this->saveIndicators($symbol, $timestamp, $adx, $atr, $roc);
            
            return [
                'adx' => $adx['adx'],
                'plus_di' => $adx['plus_di'],
                'minus_di' => $adx['minus_di'],
                'atr' => $atr,
                'roc' => $roc
            ];
            
        } catch (Exception $e) {
            error_log("Fehler bei der Indikatorberechnung: " . $e->getMessage());
            return [
                'adx' => 0,
                'plus_di' => 0,
                'minus_di' => 0,
                'atr' => 0,
                'roc' => 0
            ];
        }
    }
    
    private function calculateADX($data) {
        // Implementiere ADX-Berechnung
        return [
            'adx' => 30,
            'plus_di' => 25,
            'minus_di' => 15
        ];
    }
    
    private function calculateATR($data) {
        // Implementiere ATR-Berechnung
        return 0.02;
    }
    
    private function calculateROC($data) {
        // Implementiere ROC-Berechnung
        return 1.5;
    }
    
    private function saveIndicators($symbol, $timestamp, $adx, $atr, $roc) {
        $query = "INSERT INTO technical_analysis (market_data_id, adx, plus_di, minus_di, atr, roc)
                 SELECT id, ?, ?, ?, ?, ?
                 FROM market_data
                 WHERE symbol = ? AND timestamp = ?
                 ON DUPLICATE KEY UPDATE
                 adx = VALUES(adx),
                 plus_di = VALUES(plus_di),
                 minus_di = VALUES(minus_di),
                 atr = VALUES(atr),
                 roc = VALUES(roc)";
                 
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param(
                "dddddsi",
                $adx['adx'],
                $adx['plus_di'],
                $adx['minus_di'],
                $atr,
                $roc,
                $symbol,
                $timestamp
            );
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Fehler beim Speichern der Indikatoren: " . $e->getMessage());
        }
    }
    
    private function getDataFromDatabase($symbol, $interval, $startTime, $endTime) {
        $query = "SELECT timestamp, open, high, low, close, volume, turnover 
                 FROM market_data 
                 WHERE symbol = ? 
                 AND interval_type = ? 
                 AND timestamp BETWEEN ? AND ?
                 ORDER BY timestamp ASC";
                 
        try {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare Statement fehlgeschlagen: " . $this->db->error);
            }
            
            $stmt->bind_param("ssii", $symbol, $interval, $startTime, $endTime);
            if (!$stmt->execute()) {
                throw new Exception("Execute Statement fehlgeschlagen: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'timestamp' => (int)$row['timestamp'],
                    'open' => (float)$row['open'],
                    'high' => (float)$row['high'],
                    'low' => (float)$row['low'],
                    'close' => (float)$row['close'],
                    'volume' => (float)$row['volume'],
                    'turnover' => (float)$row['turnover']
                ];
            }
            
            $stmt->close();
            return $data;
            
        } catch (Exception $e) {
            error_log("Datenbankabfrage fehlgeschlagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function saveDataToDatabase($symbol, $interval, $data) {
        $query = "INSERT INTO market_data (symbol, interval_type, timestamp, open, high, low, close, volume, turnover) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 open = VALUES(open),
                 high = VALUES(high),
                 low = VALUES(low),
                 close = VALUES(close),
                 volume = VALUES(volume),
                 turnover = VALUES(turnover)";
                 
        try {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare Statement fehlgeschlagen: " . $this->db->error);
            }
            
            foreach ($data as $candle) {
                $stmt->bind_param("ssidddddd",
                    $symbol,
                    $interval,
                    $candle['timestamp'],
                    $candle['open'],
                    $candle['high'],
                    $candle['low'],
                    $candle['close'],
                    $candle['volume'],
                    $candle['turnover']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Execute Statement fehlgeschlagen: " . $stmt->error);
                }
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Daten speichern fehlgeschlagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function fetchDataFromAPI($symbol, $interval, $startTime, $endTime) {
        $url = "https://api.bitget.com/api/mix/v1/market/candles";
        $params = [
            'symbol' => $symbol,
            'granularity' => $this->convertInterval($interval),
            'productType' => 'UMCBL',
            'limit' => 1000,
            'endTime' => $endTime * 1000, // Konvertiere zu Millisekunden
            'startTime' => $startTime * 1000 // Konvertiere zu Millisekunden
        ];
        
        $timestamp = time() * 1000;
        $method = 'GET';
        $requestPath = '/api/mix/v1/market/candles?' . http_build_query($params);
        
        $sign = $this->generateSignature($timestamp, $method, $requestPath);
        
        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-SIGN: ' . $sign,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json'
        ];
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($response === false) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            // Wenn die Antwort ein Array ist, ist es bereits die Daten
            if (is_array($data) && !isset($data['code'])) {
                $formattedData = [];
                foreach ($data as $candle) {
                    $formattedData[] = [
                        'timestamp' => (int)($candle[0] / 1000), // Konvertiere von Millisekunden zu Sekunden
                        'open' => (float)$candle[1],
                        'high' => (float)$candle[2],
                        'low' => (float)$candle[3],
                        'close' => (float)$candle[4],
                        'volume' => (float)$candle[5],
                        'turnover' => (float)$candle[6]
                    ];
                }
                return $formattedData;
            }
            
            // Wenn die Antwort einen Fehlercode enthält
            if (isset($data['code']) && $data['code'] !== 0) {
                error_log("API-Fehler: " . print_r($data, true));
                throw new Exception('API-Fehler: ' . ($data['msg'] ?? 'Unbekannter Fehler'));
            }
            
            // Wenn die Antwort die Daten im data-Feld enthält
            if (isset($data['data']) && is_array($data['data'])) {
                $formattedData = [];
                foreach ($data['data'] as $candle) {
                    $formattedData[] = [
                        'timestamp' => (int)($candle[0] / 1000), // Konvertiere von Millisekunden zu Sekunden
                        'open' => (float)$candle[1],
                        'high' => (float)$candle[2],
                        'low' => (float)$candle[3],
                        'close' => (float)$candle[4],
                        'volume' => (float)$candle[5],
                        'turnover' => (float)$candle[6]
                    ];
                }
                return $formattedData;
            }
            
            error_log("Unerwartete API-Antwort: " . print_r($data, true));
            throw new Exception('Ungültige API-Antwort');
            
        } catch (Exception $e) {
            error_log("API-Anfrage fehlgeschlagen: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function generateSignature($timestamp, $method, $requestPath) {
        $message = $timestamp . $method . $requestPath;
        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }
    
    private function convertInterval($interval) {
        $map = [
            '1m' => '60',
            '3m' => '180',
            '5m' => '300',
            '15m' => '900',
            '30m' => '1800',
            '1h' => '3600',
            '2h' => '7200',
            '4h' => '14400',
            '6h' => '21600',
            '12h' => '43200',
            '1d' => '86400',
            '1w' => '604800'
        ];
        
        return $map[$interval] ?? '3600';
    }
}
