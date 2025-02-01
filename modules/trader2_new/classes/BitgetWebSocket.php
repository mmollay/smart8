<?php
class BitgetWebSocket {
    private $apiKey;
    private $apiSecret;
    private $passphrase;
    private $baseUrl;
    private $subscriptions = [];
    
    public function __construct($apiKey, $apiSecret, $passphrase) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->passphrase = $passphrase;
        $this->baseUrl = BITGET_WS_URL;
    }
    
    public function getConnectionConfig() {
        $timestamp = time() * 1000;
        $message = $timestamp . "GET/user/verify";
        $sign = base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));
        
        return [
            'url' => $this->baseUrl,
            'headers' => [
                'ACCESS-KEY' => $this->apiKey,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => $this->passphrase,
                'ACCESS-SIGN' => $sign
            ]
        ];
    }
    
    public function getSubscriptionMessage($symbol) {
        // Konfiguriere die Subscription für Kline/Candlestick-Daten
        $subscription = [
            "op" => "subscribe",
            "args" => [
                [
                    "channel" => "candle1m",
                    "instId" => $symbol,
                ],
                [
                    "channel" => "ticker",
                    "instId" => $symbol,
                ]
            ]
        ];
        
        $this->subscriptions[$symbol] = $subscription;
        return json_encode($subscription);
    }
    
    public function getUnsubscribeMessage($symbol) {
        if (!isset($this->subscriptions[$symbol])) {
            return null;
        }
        
        $unsubscribe = [
            "op" => "unsubscribe",
            "args" => $this->subscriptions[$symbol]['args']
        ];
        
        unset($this->subscriptions[$symbol]);
        return json_encode($unsubscribe);
    }
    
    public function getPingMessage() {
        return json_encode(["op" => "ping"]);
    }
    
    public function parseMessage($message) {
        $data = json_decode($message, true);
        
        if (!$data) {
            throw new Exception("Ungültige WebSocket-Nachricht");
        }
        
        // Behandle verschiedene Nachrichtentypen
        if (isset($data['event'])) {
            switch ($data['event']) {
                case 'subscribe':
                    return [
                        'type' => 'subscription',
                        'status' => 'success',
                        'channel' => $data['arg']['channel'] ?? null
                    ];
                case 'error':
                    return [
                        'type' => 'error',
                        'message' => $data['msg'] ?? 'Unbekannter Fehler'
                    ];
            }
        }
        
        // Verarbeite Marktdaten
        if (isset($data['data'])) {
            $channelData = $data['arg'] ?? [];
            $marketData = $data['data'][0] ?? [];
            
            switch ($channelData['channel'] ?? '') {
                case 'candle1m':
                    return [
                        'type' => 'candle',
                        'symbol' => $channelData['instId'],
                        'data' => [
                            'timestamp' => $marketData[0] ?? null,
                            'open' => $marketData[1] ?? null,
                            'high' => $marketData[2] ?? null,
                            'low' => $marketData[3] ?? null,
                            'close' => $marketData[4] ?? null,
                            'volume' => $marketData[5] ?? null
                        ]
                    ];
                    
                case 'ticker':
                    return [
                        'type' => 'ticker',
                        'symbol' => $channelData['instId'],
                        'data' => [
                            'price' => $marketData['last'] ?? null,
                            'volume' => $marketData['volCcy24h'] ?? null,
                            'timestamp' => $marketData['ts'] ?? null
                        ]
                    ];
            }
        }
        
        // Behandle Pong-Antworten
        if (isset($data['op']) && $data['op'] === 'pong') {
            return [
                'type' => 'pong',
                'timestamp' => time() * 1000
            ];
        }
        
        return [
            'type' => 'unknown',
            'raw' => $data
        ];
    }
}
