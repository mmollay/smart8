<?php
class BitGet {
    private $apiKey;
    private $secretKey;
    private $passphrase;
    private $baseUrl = 'https://api.bitget.com';
    
    public function __construct($apiKey, $secretKey, $passphrase) {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->passphrase = $passphrase;
    }
    
    public function getKlines($params) {
        $endpoint = '/api/mix/v1/market/candles';
        $queryString = http_build_query([
            'symbol' => $params['symbol'],
            'granularity' => $params['granularity'],
            'productType' => $params['productType'],
            'limit' => $params['limit'] ?? 100,
            'endTime' => $params['endTime'] ?? null,
            'startTime' => $params['startTime'] ?? null
        ]);
        
        $url = $this->baseUrl . $endpoint . '?' . $queryString;
        $timestamp = time() * 1000;
        $method = 'GET';
        
        $sign = $this->generateSignature($timestamp, $method, $endpoint . '?' . $queryString, '');
        
        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-SIGN: ' . $sign,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json'
        ];
        
        echo "Debug: Sending request to URL: " . $url . "\n";
        echo "Debug: Headers: " . print_r($headers, true) . "\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl-Fehler: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        echo "Debug: Response Code: " . $httpCode . "\n";
        
        $data = json_decode($response, true);
        echo "Debug: Response Structure: " . print_r($data, true) . "\n";
        
        if ($httpCode !== 200) {
            throw new Exception('API-Fehler: ' . $response);
        }
        
        if (!isset($data['data'])) {
            throw new Exception('Ungültige API-Antwort: ' . $response);
        }
        
        return array_reverse($data['data']); // BitGet gibt die Daten in umgekehrter Reihenfolge zurück
    }
    
    private function generateSignature($timestamp, $method, $requestPath, $body = '') {
        $message = $timestamp . $method . $requestPath . $body;
        $signature = hash_hmac('sha256', $message, $this->secretKey, true);
        return base64_encode($signature);
    }
    
    public function getAccountBalance() {
        $endpoint = '/api/mix/v1/account/accounts';
        $timestamp = time() * 1000;
        $method = 'GET';
        
        $sign = $this->generateSignature($timestamp, $method, $endpoint, '');
        
        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-SIGN: ' . $sign,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl-Fehler: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API-Fehler: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    public function getSymbolInfo($symbol) {
        $endpoint = '/api/mix/v1/market/contracts';
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl-Fehler: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API-Fehler: ' . $response);
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['data'])) {
            throw new Exception('Ungültige API-Antwort');
        }
        
        foreach ($data['data'] as $symbolInfo) {
            if ($symbolInfo['symbol'] === $symbol) {
                return $symbolInfo;
            }
        }
        
        throw new Exception('Symbol nicht gefunden');
    }
}
