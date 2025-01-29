<?php
// websocket/BitgetWebsocket.php

class BitgetWebsocket
{
    private $apiKey;
    private $apiSecret;
    private $apiPassphrase;
    private $wsEndpoint = 'wss://ws.bitget.com/mix/v1/stream';

    public function __construct($apiKey, $apiSecret, $apiPassphrase)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiPassphrase = $apiPassphrase;
    }

    public function generateAuthParams()
    {
        $timestamp = time() * 1000;
        $message = $timestamp . "GET/user/verify";
        $signature = base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));

        return [
            'op' => 'login',
            'args' => [
                [
                    'apiKey' => $this->apiKey,
                    'passphrase' => $this->apiPassphrase,
                    'timestamp' => $timestamp,
                    'sign' => $signature
                ]
            ]
        ];
    }

    // websocket/BitgetWebsocket.php

    public function getSubscriptionMessage()
    {
        return [
            'op' => 'subscribe',
            'args' => [
                [
                    'instType' => 'umcbl',    // Kleinbuchstaben!
                    'channel' => 'positions',
                    'instId' => 'ETHUSDT_UMCBL'  // Vollständiger Symbol-Name
                ],
                [
                    'instType' => 'umcbl',    // Kleinbuchstaben!
                    'channel' => 'account',
                    'instId' => 'ETHUSDT_UMCBL'  // Vollständiger Symbol-Name
                ]
            ]
        ];
    }

    public function getConfig()
    {
        return [
            'endpoint' => $this->wsEndpoint,
            'auth' => $this->generateAuthParams(),
            'subscriptions' => $this->getSubscriptionMessage()
        ];
    }
}