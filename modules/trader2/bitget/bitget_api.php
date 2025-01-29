<?php

class BitGetAPI {
    private $apiKey;
    private $apiSecret;
    private $passphrase;
    private $baseUrl = 'https://api.bitget.com';

    public function __construct($apiKey, $apiSecret, $passphrase) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->passphrase = $passphrase;
    }

    private function sign($timestamp, $method, $requestPath, $body = '') {
        $message = $timestamp . $method . $requestPath . $body;
        $signature = base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));
        return $signature;
    }

    private function request($method, $path, $params = [], $body = '') {
        $timestamp = time() * 1000;
        $url = $this->baseUrl . $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $signature = $this->sign($timestamp, $method, $path . (!empty($params) ? '?' . http_build_query($params) : ''), $body);

        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-SIGN: ' . $signature,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json'
        ];

        error_log("BitGet API Request URL: " . $url);
        error_log("BitGet API Request Headers: " . json_encode($headers));
        error_log("BitGet API Request Params: " . json_encode($params));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($body)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        error_log("BitGet API Response Code: " . $httpCode);
        error_log("BitGet API Response: " . $response);

        if ($httpCode >= 400) {
            throw new Exception('API request failed with code ' . $httpCode . ': ' . $response);
        }

        return json_decode($response, true);
    }

    private function formatSymbol($symbol) {
        // Entferne _UMCBL falls vorhanden
        $symbol = str_replace('_UMCBL', '', $symbol);
        
        // Stelle sicher, dass USDT im Symbol ist
        if (strpos($symbol, 'USDT') === false) {
            $symbol .= 'USDT';
        }
        
        // Füge _UMCBL hinzu
        return $symbol . '_UMCBL';
    }

    public function getAllTradeHistory($symbol, $startTime = null, $endTime = null, $maxBatches = 10) {
        // Stelle sicher, dass das Symbol korrekt formatiert ist
        if (strpos($symbol, '_UMCBL') === false) {
            $symbol = $this->formatSymbol($symbol);
        }
        error_log("Getting trade history for symbol: " . $symbol);
        
        // Default: Letzte 90 Tage
        if ($startTime === null) {
            $endTime = strtotime('now') * 1000;
            $startTime = strtotime('-90 days') * 1000;
        }
        
        $allTrades = [];
        $batchSize = 100;
        $lastEndTime = intval($endTime);
        $currentStartTime = intval($startTime);
        $batchCount = 0;
        
        do {
            error_log("Fetching batch " . ($batchCount + 1));
            error_log("- Start Time: " . date('Y-m-d H:i:s', intval($currentStartTime/1000)));
            error_log("- End Time: " . date('Y-m-d H:i:s', intval($lastEndTime/1000)));
            
            $queryParams = http_build_query([
                'symbol' => $symbol,
                'startTime' => $currentStartTime,
                'endTime' => $lastEndTime,
                'limit' => $batchSize
            ]);
            
            $endpoint = '/api/mix/v1/order/fills';
            $requestPath = $endpoint . '?' . $queryParams;
            error_log("BitGet trade history request path: " . $requestPath);
            
            $response = $this->request('GET', $endpoint, [
                'symbol' => $symbol,
                'startTime' => $currentStartTime,
                'endTime' => $lastEndTime,
                'limit' => $batchSize
            ]);
            
            if (!isset($response['data']) || !is_array($response['data'])) {
                error_log("Invalid response for batch: " . json_encode($response));
                break;
            }
            
            $trades = $response['data'];
            // Filtere Trades nach Symbol
            $trades = array_filter($trades, function($trade) use ($symbol) {
                return $trade['symbol'] === $symbol;
            });
            
            $tradeCount = count($trades);
            error_log("Got " . $tradeCount . " trades in batch after filtering");
            
            if ($tradeCount > 0) {
                $allTrades = array_merge($allTrades, $trades);
                
                // Finde den ältesten Trade in diesem Batch
                $oldestTime = PHP_INT_MAX;
                foreach ($trades as $trade) {
                    $tradeTime = intval($trade['cTime']);
                    if ($tradeTime < $oldestTime) {
                        $oldestTime = $tradeTime;
                    }
                }
                
                // Setze lastEndTime auf die Zeit des ältesten Trades
                $lastEndTime = $oldestTime - 1;
            }
            
            $batchCount++;
            
            // Stoppe wenn keine Trades mehr oder maximale Batches erreicht
            if ($tradeCount == 0 || $batchCount >= $maxBatches || $lastEndTime <= $startTime) {
                break;
            }
            
            // Kleine Pause zwischen den Anfragen
            usleep(100000); // 100ms
            
        } while (true);
        
        error_log("Total trades fetched: " . count($allTrades));
        return ['data' => $allTrades];
    }

    public function getPnLHistory($symbol, $startTime = null, $endTime = null, $limit = 100) {
        // Stelle sicher, dass das Symbol korrekt formatiert ist
        if (strpos($symbol, '_UMCBL') === false) {
            $symbol = $this->formatSymbol($symbol);
        }
        error_log("Getting PnL history for symbol: " . $symbol);
        
        // Default: Letzte 90 Tage
        if ($startTime === null) {
            $endTime = strtotime('now') * 1000;
            $startTime = strtotime('-90 days') * 1000;
        }
        
        $queryParams = http_build_query([
            'symbol' => $symbol,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'productType' => 'umcbl',
            'pageSize' => $limit
        ]);
        
        $endpoint = '/api/mix/v1/position/history-position';
        $requestPath = $endpoint . '?' . $queryParams;
        
        error_log("BitGet getPnLHistory request path: " . $requestPath);
        
        return $this->request('GET', $endpoint, [
            'symbol' => $symbol,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'productType' => 'umcbl',
            'pageSize' => $limit
        ]);
    }

    // Aktive Orders abrufen
    public function getActiveOrders($symbol) {
        return $this->request('GET', '/api/mix/v1/order/current', [
            'symbol' => $this->formatSymbol($symbol),
            'productType' => 'umcbl'
        ]);
    }

    // Aktive Positionen abrufen
    public function getPositions($symbol = null) {
        $params = [
            'productType' => 'umcbl'
        ];
        if ($symbol) {
            $params['symbol'] = $this->formatSymbol($symbol);
        }
        return $this->request('GET', '/api/mix/v1/position/allPosition', $params);
    }

    // Market Price abrufen
    public function getMarketPrice($symbol) {
        return $this->request('GET', '/api/mix/v1/market/ticker', [
            'symbol' => $this->formatSymbol($symbol)
        ], false);
    }

    // Order Status abrufen
    public function getOrderStatus($symbol, $orderId) {
        return $this->request('GET', '/api/mix/v1/order/detail', [
            'symbol' => $this->formatSymbol($symbol),
            'orderId' => $orderId,
            'productType' => 'umcbl'
        ]);
    }
}
