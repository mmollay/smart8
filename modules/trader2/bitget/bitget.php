<?php

class Bitget {
    private $api_key;
    private $api_secret;
    private $api_passphrase;
    private $base_url = 'https://api.bitget.com';

    public function __construct($api_key, $api_secret, $api_passphrase) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->api_passphrase = $api_passphrase;
    }

    public function get_api_key() {
        return $this->api_key;
    }

    public function get_api_secret() {
        return $this->api_secret;
    }

    public function get_api_passphrase() {
        return $this->api_passphrase;
    }

    public function get_open_orders($symbol = null) {
        $endpoint = '/api/mix/v1/order/current';
        $params = [
            'productType' => 'umcbl'
        ];
        if ($symbol) {
            $params['symbol'] = $symbol . '_UMCBL';
        }
        
        return $this->send_request('GET', $endpoint, $params);
    }

    public function get_positions($symbol = null) {
        $endpoint = '/api/mix/v1/position/allPosition';
        $params = [
            'productType' => 'umcbl'
        ];
        if ($symbol) {
            $params['symbol'] = $symbol . '_UMCBL';
        }
        
        return $this->send_request('GET', $endpoint, $params);
    }

    public function place_order($params) {
        // Hauptorder platzieren
        $main_order = $this->place_single_order([
            'symbol' => $params['symbol'] . '_UMCBL',
            'marginCoin' => 'USDT',
            'size' => $params['size'],
            'price' => $params['price'],
            'side' => strtolower($params['side']) === 'buy' ? 'open_long' : 'open_short',
            'orderType' => 'limit',
            'timeInForceValue' => 'normal',
            'leverage' => $params['leverage']
        ]);

        if (isset($main_order['error'])) {
            return $main_order;
        }

        $order_ids = ['main' => $main_order['data']['orderId']];

        // Take Profit Order
        if (!empty($params['takeProfit'])) {
            $tp_result = $this->place_plan_order([
                'symbol' => $params['symbol'] . '_UMCBL',
                'marginCoin' => 'USDT',
                'size' => $params['size'],
                'triggerPrice' => $params['takeProfit'],
                'executePrice' => $params['takeProfit'],
                'triggerType' => 'fill_price',
                'side' => strtolower($params['side']) === 'buy' ? 'close_long' : 'close_short',
                'orderType' => 'limit',
                'planType' => 'profit_plan'
            ]);
            if (!isset($tp_result['error'])) {
                $order_ids['tp'] = $tp_result['data']['orderId'];
            }
        }

        // Stop Loss Order
        if (!empty($params['stopLoss'])) {
            $sl_result = $this->place_plan_order([
                'symbol' => $params['symbol'] . '_UMCBL',
                'marginCoin' => 'USDT',
                'size' => $params['size'],
                'triggerPrice' => $params['stopLoss'],
                'executePrice' => $params['stopLoss'],
                'triggerType' => 'fill_price',
                'side' => strtolower($params['side']) === 'buy' ? 'close_long' : 'close_short',
                'orderType' => 'limit',
                'planType' => 'loss_plan'
            ]);
            if (!isset($sl_result['error'])) {
                $order_ids['sl'] = $sl_result['data']['orderId'];
            }
        }

        return $order_ids;
    }

    public function cancel_order($params) {
        if (isset($params['planOrderId'])) {
            return $this->send_request('/api/mix/v1/plan/cancelPlan', 'POST', $params);
        } else {
            return $this->send_request('/api/mix/v1/order/cancel-order', 'POST', $params);
        }
    }

    private function place_single_order($order) {
        return $this->send_request('/api/mix/v1/order/placeOrder', 'POST', $order);
    }

    private function place_plan_order($order) {
        return $this->send_request('/api/mix/v1/plan/placePlan', 'POST', $order);
    }

    private function send_request($endpoint, $method, $params = []) {
        $timestamp = time() * 1000;
        $body = json_encode($params);
        
        $sign = $this->generate_signature($endpoint, $method, $timestamp, $body);
        
        $headers = [
            'ACCESS-KEY: ' . $this->api_key,
            'ACCESS-SIGN: ' . $sign,
            'ACCESS-TIMESTAMP: ' . $timestamp,
            'ACCESS-PASSPHRASE: ' . $this->api_passphrase,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            return ['error' => 'HTTP Error: ' . $http_code . ' Response: ' . $result];
        }

        $result_array = json_decode($result, true);
        if (!$result_array || $result_array['code'] !== '00000') {
            return ['error' => 'API Error: ' . ($result_array['msg'] ?? 'Unknown error')];
        }

        return $result_array;
    }

    private function generate_signature($endpoint, $method, $timestamp, $body) {
        $message = $timestamp . $method . $endpoint . $body;
        return base64_encode(hash_hmac('sha256', $message, $this->api_secret, true));
    }
}
