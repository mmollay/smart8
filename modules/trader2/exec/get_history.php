<?php

class BitgetClient
{
    private string $apiKey;
    private string $apiSecret;
    private string $passphrase;
    private string $baseUrl = 'https://api.bitget.com';

    public function __construct(string $apiKey, string $apiSecret, string $passphrase)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->passphrase = $passphrase;
    }

    private function generateSignature(string $timestamp, string $method, string $requestPath, string $body = ''): string
    {
        $message = $timestamp . $method . $requestPath . $body;
        return base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));
    }

    private function createTimestamp(): string
    {
        return (string) (round(microtime(true) * 1000));
    }

    public function getPositionHistory(
        string $symbol = 'ETHUSDT_UMCBL',
        ?int $startTime = null,
        ?int $endTime = null
    ): array {
        $method = 'GET';
        $endpoint = '/api/mix/v1/position/history-position';
        $timestamp = $this->createTimestamp();

        if (!$startTime)
            $startTime = strtotime('-7 days');
        if (!$endTime)
            $endTime = time();

        $queryParams = http_build_query([
            'symbol' => $symbol,
            'startTime' => $startTime * 1000,
            'endTime' => $endTime * 1000,
            'productType' => 'umcbl'
        ]);

        $requestPath = $endpoint . '?' . $queryParams;
        $sign = $this->generateSignature($timestamp, $method, $requestPath);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $requestPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'ACCESS-KEY: ' . $this->apiKey,
                'ACCESS-SIGN: ' . $sign,
                'ACCESS-TIMESTAMP: ' . $timestamp,
                'ACCESS-PASSPHRASE: ' . $this->passphrase,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Ausführen und JSON ausgeben
$client = new BitgetClient(
    'bg_cc89302322ccb5c2c3942f70dfbd8d2e',
    'c034f42fe42bec1b57982ee642fbf3f339c9b4eb6dd5ff0a68f81fd11ee0cde2',
    'MCmaster23'
);

$history = $client->getPositionHistory('ETHUSDT_UMCBL');
echo json_encode($history, JSON_PRETTY_PRINT);