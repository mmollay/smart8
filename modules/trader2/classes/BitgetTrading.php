<?php
class BitgetTrading
{
    private $apiKey;
    private $apiSecret;
    private $apiPassphrase;
    private $baseUrl = 'https://api.bitget.com';

    public function __construct($apiKey, $apiSecret, $apiPassphrase)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiPassphrase = $apiPassphrase;
    }

    private function generateSignature($timestamp, $method, $requestPath, $body = '')
    {
        $message = $timestamp . $method . $requestPath . $body;
        return base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));
    }

    private function makeRequest($method, $endpoint, $params = [], $isBody = false)
    {
        $timestamp = time() * 1000;
        $requestPath = $endpoint;
        $body = '';
        $url = $this->baseUrl . $endpoint;

        if ($isBody) {
            $body = json_encode($params);
        } else if (!empty($params)) {
            $queryString = http_build_query($params);
            $requestPath .= '?' . $queryString;
            $url .= '?' . $queryString;
        }

        $signature = $this->generateSignature($timestamp, $method, $requestPath, $body);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'ACCESS-KEY: ' . $this->apiKey,
                'ACCESS-SIGN: ' . $signature,
                'ACCESS-TIMESTAMP: ' . $timestamp,
                'ACCESS-PASSPHRASE: ' . $this->apiPassphrase,
                'Content-Type: application/json'
            ],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if ($isBody && !empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response, true);

        // Die Bitget API gibt für Klines direkt ein Array zurück
        if ($endpoint === '/api/mix/v1/market/candles') {
            return $data;
        }

        if (!$data) {
            throw new Exception('Invalid JSON response: ' . $response);
        }

        // Fehlerprüfung für nicht-Klines Endpoints
        if (isset($data['code']) && $data['code'] !== '00000' && !is_array($data)) {
            throw new Exception('API Error: ' . ($data['msg'] ?? 'Unknown error'));
        }

        return $data;
    }

    public function getKlines($symbol = 'ETHUSDT_UMCBL', $interval = '15m', $limit = 100)
    {
        try {
            // Debug-Log
            error_log("Getting klines for symbol: " . $symbol);

            $timestamp = time() * 1000;
            $params = [
                'symbol' => $symbol,
                'granularity' => $interval,
                'limit' => $limit
            ];

            $requestPath = '/api/mix/v1/market/ticker';
            $method = 'GET';

            // Signatur generieren
            $message = $timestamp . $method . $requestPath;
            $signature = base64_encode(hash_hmac('sha256', $message, $this->apiSecret, true));

            // CURL Request für aktuellen Preis
            $ch = curl_init();
            $url = $this->baseUrl . $requestPath . '?symbol=' . $symbol;

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'ACCESS-KEY: ' . $this->apiKey,
                    'ACCESS-SIGN: ' . $signature,
                    'ACCESS-TIMESTAMP: ' . $timestamp,
                    'ACCESS-PASSPHRASE: ' . $this->apiPassphrase,
                    'Content-Type: application/json'
                ]
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }

            curl_close($ch);

            $data = json_decode($response, true);
            error_log("Ticker response: " . json_encode($data));

            if (!isset($data['data'])) {
                throw new Exception("No ticker data available");
            }

            // Aktuellen Preis aus dem Ticker extrahieren
            $currentPrice = floatval($data['data']['last']);
            error_log("Current price from ticker: " . $currentPrice);

            // Struktur für die weitere Verarbeitung beibehalten
            return [
                'data' => [
                    [
                        time() * 1000,  // timestamp
                        $currentPrice,   // open
                        $currentPrice,   // high
                        $currentPrice,   // low
                        $currentPrice,   // close
                        0,              // volume
                        0               // turnover
                    ]
                ],
                'currentPrice' => $currentPrice
            ];

        } catch (Exception $e) {
            error_log("Error in getKlines: " . $e->getMessage());
            throw $e;
        }
    }

    public function setPositionMode($symbol, $mode = 'single_side')
    {
        return $this->makeRequest('POST', '/api/mix/v1/account/setPositionMode', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'positionMode' => $mode
        ], true);
    }

    public function setLeverage($symbol, $leverage)
    {
        return $this->makeRequest('POST', '/api/mix/v1/account/setLeverage', [
            'symbol' => $symbol,
            'marginCoin' => 'USDT',
            'leverage' => $leverage
        ], true);
    }

    public function getAccountInfo($symbol = 'ETHUSDT_UMCBL')
    {
        try {
            // Account Details abrufen
            $response = $this->makeRequest('GET', '/api/mix/v1/account/account', [
                'symbol' => $symbol,
                'marginCoin' => 'USDT'
            ]);

            if (!isset($response['data']) || !is_array($response['data'])) {
                throw new Exception('Invalid account data response');
            }

            // Position Details abrufen
            $positionResponse = $this->makeRequest('GET', '/api/mix/v1/position/allPosition', [
                'productType' => 'UMCBL',
                'marginCoin' => 'USDT'
            ]);

            // Berechne das gesamte Position Margin
            $totalPositionMargin = 0;
            if (isset($positionResponse['data'])) {
                foreach ($positionResponse['data'] as $position) {
                    if ($position['holdSide'] !== 'long' && $position['holdSide'] !== 'short')
                        continue;
                    $totalPositionMargin += floatval($position['margin']);
                }
            }

            $accountData = $response['data'];

            return [
                'data' => [
                    'equity' => floatval($accountData['equity']),           // Gesamtkapital
                    'available' => floatval($accountData['available']),     // Verfügbares Kapital
                    'unrealizedPL' => floatval($accountData['unrealizedPL']), // Unrealisierter PnL
                    'realizedPL' => floatval($accountData['realizePL'] ?? 0),  // Realisierter PnL
                    'positionMargin' => $totalPositionMargin,              // Position Margin
                    'orderMargin' => floatval($accountData['locked'] ?? 0), // Order Margin
                    'marginCoin' => 'USDT',
                    'marginMode' => $accountData['marginMode'] ?? 'fixed'
                ]
            ];
        } catch (Exception $e) {
            error_log("Error in getAccountInfo: " . $e->getMessage());
            throw $e;
        }
    }
    private function analyzeTrend($klines)
    {
        try {
            if (!isset($klines['data']) || empty($klines['data'])) {
                throw new Exception("No klines data available");
            }

            // Schlusskurse aus Klines extrahieren
            $closes = array_map(function ($candle) {
                return floatval($candle[4]); // Schlusskurs ist an Index 4
            }, $klines['data']);

            if (empty($closes)) {
                throw new Exception("Keine Schlusskurse verfügbar");
            }

            // Technische Indikatoren berechnen
            $rsi = $this->calculateRSI($closes);
            $ema20 = $this->calculateEMA($closes, 20);
            $ema50 = $this->calculateEMA($closes, 50);
            $currentPrice = $closes[count($closes) - 1];

            // Scoring System
            $score = 50; // Neutraler Ausgangspunkt

            // RSI Analyse
            if ($rsi < 30)
                $score += 20;
            elseif ($rsi > 70)
                $score -= 20;
            elseif ($rsi < 45)
                $score += 10;
            elseif ($rsi > 55)
                $score -= 10;

            // EMA Analyse
            if ($ema20 > $ema50) {
                $score += 15;
            } else {
                $score -= 15;
            }

            if ($currentPrice > $ema20 && $currentPrice > $ema50) {
                $score += 15;
            } elseif ($currentPrice < $ema20 && $currentPrice < $ema50) {
                $score -= 15;
            }

            return [
                'score' => $score,
                'currentPrice' => $currentPrice,
                'rsi' => $rsi,
                'ema20' => $ema20,
                'ema50' => $ema50
            ];

        } catch (Exception $e) {
            error_log("Error in analyzeTrend: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateRSI($closes, $period = 14)
    {
        if (count($closes) < $period + 1) {
            return 50; // Neutraler Wert, wenn nicht genug Daten
        }

        $gains = [];
        $losses = [];

        // Gewinne und Verluste berechnen
        for ($i = 1; $i < count($closes); $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[] = max(0, $change);
            $losses[] = max(0, -$change);
        }

        // Durchschnittlichen Gewinn und Verlust berechnen
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        // RSI berechnen
        if ($avgLoss == 0) {
            return 100;
        }

        return 100 - (100 / (1 + ($avgGain / $avgLoss)));
    }

    private function calculateEMA($closes, $period = 20)
    {
        if (count($closes) < $period) {
            return $closes[count($closes) - 1]; // Letzter Preis wenn nicht genug Daten
        }

        $multiplier = 2 / ($period + 1);
        $ema = array_sum(array_slice($closes, 0, $period)) / $period; // Simple MA als Start

        foreach (array_slice($closes, $period) as $close) {
            $ema = ($close - $ema) * $multiplier + $ema;
        }

        return $ema;
    }

    public function placeFutureTrade($params)
    {
        try {
            error_log("Placing trade with parameters: " . json_encode($params));

            // Wenn keine Side angegeben, Analyse durchführen
            if (!isset($params['side'])) {
                $klines = $this->getKlines($params['symbol']);
                $analysis = $this->analyzeTrend($klines);

                $params['side'] = $analysis['score'] >= 50 ? 'buy' : 'sell';
                $params['price'] = number_format($analysis['currentPrice'], 2, '.', '');
                $params['takeProfit'] = number_format($analysis['score'] >= 50 ?
                    $analysis['currentPrice'] * 1.015 : $analysis['currentPrice'] * 0.985, 2, '.', '');
                $params['stopLoss'] = number_format($analysis['score'] >= 50 ?
                    $analysis['currentPrice'] * 0.99 : $analysis['currentPrice'] * 1.01, 2, '.', '');
            }

            // Position Mode setzen
            $this->setPositionMode($params['symbol']);

            // Hebel setzen
            $this->setLeverage($params['symbol'], $params['leverage']);

            // Seitenparameter für Orders
            $side = $params['side'] === 'buy' ? 'open_long' : 'open_short';
            $closeSide = $params['side'] === 'buy' ? 'close_long' : 'close_short';

            // Hauptorder Parameter
            $mainOrderParams = [
                'symbol' => $params['symbol'],
                'marginCoin' => 'USDT',
                'size' => number_format($params['size'], 3, '.', ''),  // 3 Dezimalstellen für Size
                'price' => number_format($params['price'], 2, '.', ''), // 2 Dezimalstellen für Preis
                'side' => $side,
                'orderType' => 'limit',
                'timeInForceValue' => 'normal'
            ];

            error_log("Main order parameters: " . json_encode($mainOrderParams));

            // Hauptorder platzieren
            $mainOrder = $this->makeRequest('POST', '/api/mix/v1/order/placeOrder', $mainOrderParams, true);
            $orders = ['mainOrder' => $mainOrder];

            // Take Profit Order
            if (isset($params['takeProfit']) && $params['takeProfit'] > 0) {
                $tpParams = [
                    'symbol' => $params['symbol'],
                    'marginCoin' => 'USDT',
                    'size' => number_format($params['size'], 3, '.', ''),
                    'triggerPrice' => number_format($params['takeProfit'], 2, '.', ''),
                    'executePrice' => number_format($params['takeProfit'], 2, '.', ''),
                    'side' => $closeSide,
                    'orderType' => 'limit',
                    'triggerType' => 'market_price',
                    'planType' => 'profit_plan'
                ];

                $orders['takeProfitOrder'] = $this->makeRequest('POST', '/api/mix/v1/plan/placePlan', $tpParams, true);
            }

            // Stop Loss Order
            if (isset($params['stopLoss']) && $params['stopLoss'] > 0) {
                $slParams = [
                    'symbol' => $params['symbol'],
                    'marginCoin' => 'USDT',
                    'size' => number_format($params['size'], 3, '.', ''),
                    'triggerPrice' => number_format($params['stopLoss'], 2, '.', ''),
                    'executePrice' => number_format($params['stopLoss'], 2, '.', ''),
                    'side' => $closeSide,
                    'orderType' => 'market',
                    'triggerType' => 'market_price',
                    'planType' => 'loss_plan'
                ];

                $orders['stopLossOrder'] = $this->makeRequest('POST', '/api/mix/v1/plan/placePlan', $slParams, true);
            }

            return [
                'success' => true,
                'orders' => $orders,
                'analysis' => $analysis ?? null
            ];

        } catch (Exception $e) {
            error_log("Error placing trade: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'details' => isset($orders) ? json_encode($orders) : null
            ];
        }
    }

    public function getTradeRecommendation($symbol = 'ETHUSDT_UMCBL')
    {
        $klines = $this->getKlines($symbol);
        $analysis = $this->analyzeTrend($klines);

        return [
            'action' => $analysis['score'] >= 50 ? 'buy' : 'sell',
            'confidence' => abs($analysis['score'] - 50),
            'current_price' => $analysis['currentPrice'],
            'suggested_entry' => $analysis['currentPrice'],
            'suggested_tp' => $analysis['score'] >= 50 ?
                $analysis['currentPrice'] * 1.015 :
                $analysis['currentPrice'] * 0.985,
            'suggested_sl' => $analysis['score'] >= 50 ?
                $analysis['currentPrice'] * 0.99 :
                $analysis['currentPrice'] * 1.01,
            'analysis' => [
                'rsi' => round($analysis['rsi'], 2),
                'ema20' => round($analysis['ema20'], 2),
                'ema50' => round($analysis['ema50'], 2)
            ],
            'reasoning' => $this->generateReasoning($analysis)
        ];
    }

    private function generateReasoning($analysis)
    {
        $reasons = [];

        if ($analysis['rsi'] < 30) {
            $reasons[] = "RSI zeigt überverkaufte Bedingungen (" . round($analysis['rsi'], 2) . ")";
        } elseif ($analysis['rsi'] > 70) {
            $reasons[] = "RSI zeigt überkaufte Bedingungen (" . round($analysis['rsi'], 2) . ")";
        }

        if ($analysis['ema20'] > $analysis['ema50']) {
            $reasons[] = "EMA20 über EMA50 deutet auf Aufwärtstrend hin";
        } else {
            $reasons[] = "EMA20 unter EMA50 deutet auf Abwärtstrend hin";
        }

        if ($analysis['currentPrice'] > $analysis['ema20'] && $analysis['currentPrice'] > $analysis['ema50']) {
            $reasons[] = "Preis über beiden EMAs zeigt starken Aufwärtstrend";
        } elseif ($analysis['currentPrice'] < $analysis['ema20'] && $analysis['currentPrice'] < $analysis['ema50']) {
            $reasons[] = "Preis unter beiden EMAs zeigt starken Abwärtstrend";
        }

        return $reasons;
    }

    // Hilfsfunktion zum Validieren der Handelsparameter
    private function validateTradeParams($params)
    {
        $required = ['symbol', 'size', 'leverage'];

        foreach ($required as $field) {
            if (!isset($params[$field])) {
                throw new Exception("Fehlender Parameter: $field");
            }
        }

        if (isset($params['leverage']) && ($params['leverage'] < 1 || $params['leverage'] > 100)) {
            throw new Exception("Ungültiger Hebel. Muss zwischen 1 und 100 liegen.");
        }

        if (isset($params['size']) && $params['size'] <= 0) {
            throw new Exception("Ungültige Positionsgröße. Muss größer als 0 sein.");
        }

        return true;
    }

    // Funktion zum Abrufen offener Positionen
    public function getOpenPositions($symbol = 'ETHUSDT_UMCBL')
    {
        try {
            $response = $this->makeRequest('GET', '/api/mix/v1/position/allPosition', [
                'productType' => 'umcbl',
                'marginCoin' => 'USDT'
            ]);

            if (!isset($response['data'])) {
                throw new Exception("Keine Positionsdaten in der API-Antwort");
            }

            // Filtere nur die Positionen für das angegebene Symbol
            return array_filter($response['data'], function ($position) use ($symbol) {
                return $position['symbol'] === $symbol;
            });

        } catch (Exception $e) {
            error_log("Fehler beim Abrufen der Positionen: " . $e->getMessage());
            throw $e;
        }
    }

    // Funktion zum Abrufen offener Orders
    public function getOpenOrders($symbol = 'ETHUSDT_UMCBL')
    {
        try {
            return $this->makeRequest('GET', '/api/mix/v1/order/current', [
                'symbol' => $symbol
            ]);
        } catch (Exception $e) {
            error_log("Fehler beim Abrufen der offenen Orders: " . $e->getMessage());
            throw $e;
        }
    }

    // Funktion zum Schließen einer Position
    public function closePosition($symbol, $size, $positionSide = 'long')
    {
        try {
            $side = $positionSide === 'long' ? 'close_long' : 'close_short';

            return $this->makeRequest('POST', '/api/mix/v1/order/placeOrder', [
                'symbol' => $symbol,
                'marginCoin' => 'USDT',
                'size' => $size,
                'side' => $side,
                'orderType' => 'market',
                'timeInForceValue' => 'normal'
            ], true);
        } catch (Exception $e) {
            error_log("Fehler beim Schließen der Position: " . $e->getMessage());
            throw $e;
        }
    }

    // Funktion zum Abbrechen einer Order
    public function cancelOrder($symbol, $orderId)
    {
        try {
            return $this->makeRequest('POST', '/api/mix/v1/order/cancel-order', [
                'symbol' => $symbol,
                'orderId' => $orderId
            ], true);
        } catch (Exception $e) {
            error_log("Fehler beim Abbrechen der Order: " . $e->getMessage());
            throw $e;
        }
    }

    // Funktion zum Abrufen des Handelsverlaufs
    public function getTradeHistory($symbol = 'ETHUSDT_UMCBL', $limit = 100)
    {
        try {
            return $this->makeRequest('GET', '/api/mix/v1/order/history', [
                'symbol' => $symbol,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            error_log("Fehler beim Abrufen des Handelsverlaufs: " . $e->getMessage());
            throw $e;
        }
    }
}