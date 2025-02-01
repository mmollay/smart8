<?php
class BitgetTrading {
    private $apiKey;
    private $apiSecret;
    private $passphrase;
    private $baseUrl;
    
    public function __construct($apiKey, $apiSecret, $passphrase) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->passphrase = $passphrase;
        $this->baseUrl = BITGET_API_URL;
    }
    
    public function placeFutureTrade($params) {
        try {
            // Parameter validieren
            $requiredParams = ['symbol', 'side', 'size', 'price', 'leverage'];
            foreach ($requiredParams as $param) {
                if (!isset($params[$param])) {
                    throw new Exception("Fehlender Parameter: {$param}");
                }
            }
            
            // Order vorbereiten
            $endpoint = '/api/mix/v1/order/placeOrder';
            $timestamp = time() * 1000;
            
            $data = [
                'symbol' => $params['symbol'],
                'marginCoin' => 'USDT',
                'side' => $params['side'],
                'size' => (string)$params['size'],
                'price' => (string)$params['price'],
                'orderType' => 'limit',
                'timeInForceValue' => 'normal'
            ];
            
            // Take-Profit und Stop-Loss hinzufügen wenn vorhanden
            if (isset($params['takeProfit'])) {
                $data['takeProfit'] = (string)$params['takeProfit'];
            }
            if (isset($params['stopLoss'])) {
                $data['stopLoss'] = (string)$params['stopLoss'];
            }
            
            // Leverage setzen
            $this->setLeverage($params['symbol'], $params['leverage']);
            
            // Order platzieren
            $response = $this->sendRequest('POST', $endpoint, $data);
            
            if (!isset($response['data'])) {
                throw new Exception("Ungültige API-Antwort: " . json_encode($response));
            }
            
            return [
                'orderId' => $response['data']['orderId'],
                'clientOid' => $response['data']['clientOid'] ?? null,
                'status' => $response['data']['state'] ?? 'placed'
            ];
            
        } catch (Exception $e) {
            logError("BitGet Trade Fehler", [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw $e;
        }
    }
    
    private function setLeverage($symbol, $leverage) {
        $endpoint = '/api/mix/v1/account/setLeverage';
        $data = [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'leverage' => (string)$leverage
        ];
        
        return $this->sendRequest('POST', $endpoint, $data);
    }
    
    private function sendRequest($method, $endpoint, $data = null) {
        $timestamp = time() * 1000;
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'ACCESS-KEY: ' . $this->apiKey,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->passphrase,
            'Content-Type: application/json'
        ];
        
        $body = $data ? json_encode($data) : '';
        $sign = $this->generateSignature($timestamp, $method, $endpoint, $body);
        $headers[] = 'ACCESS-SIGN: ' . $sign;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error {$httpCode}: {$response}");
        }
        
        return json_decode($response, true);
    }
    
    private function generateSignature($timestamp, $method, $endpoint, $body) {
        $message = $timestamp . $method . $endpoint . $body;
        return base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));
    }
}
