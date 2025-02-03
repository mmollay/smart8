<?php
/**
 * BitgetAPI Klasse
 * Verwaltet alle Interaktionen mit der Bitget API
 */
class BitgetAPI {
    private $apiKey;
    private $secretKey;
    private $passphrase;
    private $baseUrl = 'https://api.bitget.com';

    public function __construct() {
        $this->apiKey = BITGET_API_KEY;
        $this->secretKey = BITGET_SECRET_KEY;
        $this->passphrase = BITGET_PASSPHRASE;
        
        if (empty($this->apiKey) || empty($this->secretKey) || empty($this->passphrase)) {
            throw new Exception('API Credentials nicht konfiguriert');
        }
    }

    /**
     * Generiert die Signatur für API-Requests
     */
    private function generateSignature($timestamp, $method, $requestPath, $body = '') {
        $message = $timestamp . $method . $requestPath . $body;
        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }

    /**
     * Führt einen API-Request aus
     */
    private function request($method, $endpoint, $params = [], $isPrivate = true) {
        $url = $this->baseUrl . $endpoint;
        $timestamp = time() . '000';
        $body = '';

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        } else if ($method === 'POST') {
            $body = json_encode($params);
        }

        $headers = [
            'Content-Type: application/json',
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'ACCESS-SIGN: ' . $this->generateSignature($timestamp, $method, $endpoint, $body)
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API Request fehlgeschlagen: HTTP $httpCode - $response");
        }

        return json_decode($response, true);
    }

    /**
     * Holt den aktuellen Preis eines Symbols
     */
    public function getSymbolPrice($symbol) {
        return $this->request('GET', '/api/mix/v1/market/ticker', [
            'symbol' => strtoupper($symbol),
            'productType' => 'UMCBL'
        ], false);
    }

    /**
     * Holt Kerzendaten
     */
    public function getKlines($symbol, $interval = '5m', $limit = 100) {
        return $this->request('GET', '/api/mix/v1/market/candles', [
            'symbol' => strtoupper($symbol),
            'granularity' => $interval,
            'productType' => 'UMCBL',
            'limit' => (int)$limit
        ], false);
    }

    /**
     * Holt Account-Informationen
     */
    public function getAccountInfo($symbol) {
        return $this->request('GET', '/api/mix/v1/account/account', ['symbol' => $symbol]);
    }

    /**
     * Setzt einen Market-Order
     */
    public function placeMarketOrder($symbol, $side, $size, $leverage) {
        return $this->request('POST', '/api/mix/v1/order/placeOrder', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'side' => $side,
            'orderType' => 'market',
            'size' => $size,
            'timeInForceValue' => 'normal',
            'leverage' => $leverage
        ]);
    }

    /**
     * Setzt einen Limit-Order
     */
    public function placeLimitOrder($symbol, $side, $size, $price, $leverage) {
        return $this->request('POST', '/api/mix/v1/order/placeOrder', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'side' => $side,
            'orderType' => 'limit',
            'size' => $size,
            'price' => $price,
            'timeInForceValue' => 'normal',
            'leverage' => $leverage
        ]);
    }

    /**
     * Setzt einen Stop-Loss
     */
    public function placeStopLoss($symbol, $side, $size, $triggerPrice, $leverage) {
        return $this->request('POST', '/api/mix/v1/order/placeOrder', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'side' => $side,
            'orderType' => 'market',
            'size' => $size,
            'triggerPrice' => $triggerPrice,
            'timeInForceValue' => 'normal',
            'leverage' => $leverage,
            'triggerType' => 'market_price'
        ]);
    }

    /**
     * Setzt einen Take-Profit
     */
    public function placeTakeProfit($symbol, $side, $size, $triggerPrice, $leverage) {
        return $this->request('POST', '/api/mix/v1/order/placeOrder', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'side' => $side,
            'orderType' => 'market',
            'size' => $size,
            'triggerPrice' => $triggerPrice,
            'timeInForceValue' => 'normal',
            'leverage' => $leverage,
            'triggerType' => 'market_price'
        ]);
    }

    /**
     * Schließt eine Position
     */
    public function closePosition($symbol, $side, $size) {
        return $this->request('POST', '/api/mix/v1/order/closePosition', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'side' => $side,
            'size' => $size
        ]);
    }

    /**
     * Setzt den Hebel für ein Symbol
     */
    public function setLeverage($symbol, $leverage) {
        return $this->request('POST', '/api/mix/v1/account/setLeverage', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'leverage' => $leverage
        ]);
    }

    /**
     * Holt offene Positionen
     */
    public function getPositions($symbol) {
        return $this->request('GET', '/api/mix/v1/position/singlePosition', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT'
        ]);
    }

    /**
     * Holt offene Orders
     */
    public function getOpenOrders($symbol) {
        return $this->request('GET', '/api/mix/v1/order/current', [
            'symbol' => $symbol
        ]);
    }

    /**
     * Holt die Orderhistorie
     */
    public function getOrderHistory($symbol, $startTime = null, $endTime = null, $limit = 100) {
        $params = ['symbol' => $symbol, 'limit' => $limit];
        if ($startTime) $params['startTime'] = $startTime;
        if ($endTime) $params['endTime'] = $endTime;
        
        return $this->request('GET', '/api/mix/v1/order/history', $params);
    }

    /**
     * Hole Kontostand
     */
    public function getAccountBalance() {
        $endpoint = '/api/mix/v1/account/accounts';
        $params = [
            'productType' => 'UMCBL'
        ];
        
        try {
            $response = $this->request('GET', $endpoint, $params);
            error_log('Account Balance Response: ' . print_r($response, true));
            
            if (!isset($response['data']) || !is_array($response['data'])) {
                throw new Exception('Keine Kontodaten in der API-Antwort');
            }
            
            return $response;
            
        } catch (Exception $e) {
            error_log('BitGet API Exception in getAccountBalance: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hole aktuellen Preis für ein Symbol
     */
    public function getSymbolPriceNew($symbol) {
        $endpoint = '/api/mix/v1/market/ticker';
        $params = [
            'symbol' => strtoupper($symbol)
        ];
        
        try {
            $response = $this->request('GET', $endpoint, $params);
            error_log('Price Response: ' . print_r($response, true));
            
            if (!isset($response['data'])) {
                throw new Exception('Keine Preisdaten in der API-Antwort');
            }
            
            return $response;
            
        } catch (Exception $e) {
            error_log('BitGet API Exception in getSymbolPrice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hole Kerzendaten
     */
    public function getKlinesNew($symbol, $interval = '5m', $limit = 100) {
        $endpoint = '/api/mix/v1/market/candles';
        $params = [
            'symbol' => strtoupper($symbol),
            'granularity' => strtoupper($interval),
            'limit' => (int)$limit,
            'productType' => 'UMCBL'
        ];
        
        try {
            $response = $this->request('GET', $endpoint, $params);
            error_log('Klines Response: ' . print_r($response, true));
            
            if (!isset($response['data'])) {
                throw new Exception('Keine Klines-Daten in der API-Antwort');
            }
            
            return $response;
            
        } catch (Exception $e) {
            error_log('BitGet API Exception in getKlines: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sende Request an die API
     */
    private function sendRequest($method, $endpoint, $params = [], $retry = 3) {
        $timestamp = (string)(time() * 1000);
        $requestPath = $endpoint;
        $body = '';

        // Sortiere Parameter alphabetisch für konsistente Signatur
        if (!empty($params)) {
            ksort($params);
            if ($method === 'GET') {
                $requestPath .= '?' . http_build_query($params);
            } else {
                $body = json_encode($params);
            }
        }

        // Generiere Signatur
        $message = $timestamp . strtoupper($method) . $requestPath . $body;
        $signature = base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));

        // Setze Header
        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-SIGN: ' . $signature,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json',
            'X-CHANNEL-API-CODE: y3qc5c91bv'
        ];

        // Baue URL
        $url = $this->baseUrl . $requestPath;
        
        error_log("API Request:\nURL: $url\nMethod: $method\nHeaders: " . print_r($headers, true));

        // Sende Request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        if ($method === 'POST' && !empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        error_log("API Response:\nHTTP Code: $httpCode\nResponse: $response\nError: $error");
        
        curl_close($ch);

        if ($error) {
            throw new Exception("API Request fehlgeschlagen: $error");
        }

        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMessage = isset($data['msg']) ? $data['msg'] : 'Unbekannter API-Fehler';
            throw new Exception("API Request fehlgeschlagen: HTTP $httpCode - " . json_encode($data));
        }

        if (!isset($data['code']) || $data['code'] !== '00000') {
            throw new Exception('API Error: ' . ($data['msg'] ?? 'Unbekannter Fehler'));
        }

        return $data;
    }
}
